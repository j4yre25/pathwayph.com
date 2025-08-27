<?php

namespace App\Http\Controllers;

use App\Models\JobInvitation;
use App\Models\Graduate;
use App\Models\Job;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ManageJobReferralsController extends Controller
{
    public function index(Request $request)
    {
        // Advanced filtering and search
        $query = \App\Models\JobInvitation::with(['graduate.user', 'job.company', 'graduate.graduateSkills.skill', 'job.programs']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('company')) {
            $query->whereHas('job.company', function ($q) use ($request) {
                $q->where('company_name', 'like', '%' . $request->company . '%');
            });
        }
        // Filtering by candidate (old)
        if ($request->filled('candidate')) {
            $query->whereHas('graduate', function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->candidate . '%')
                    ->orWhere('middle_name', 'like', '%' . $request->candidate . '%')
                    ->orWhere('last_name', 'like', '%' . $request->candidate . '%');
            });
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('graduate', function ($sub) use ($search) {
                    $sub->where('first_name', 'like', "%$search%")
                        ->orWhere('middle_name', 'like', "%$search%")
                        ->orWhere('last_name', 'like', "%$search%");
                })
                    ->orWhereHas('job', fn($sub) => $sub->where('job_title', 'like', "%$search%"))
                    ->orWhereHas('job.company', fn($sub) => $sub->where('company_name', 'like', "%$search%"));
            });
        }

        // Pagination (10 per page)
        $referrals = $query->latest()->paginate(10)->withQueryString();

        // Calculate success rate
        $totalReferrals = $referrals->total();
        $successfulReferrals = $query->clone()->where('status', 'hired')->count();
        $successRate = $totalReferrals > 0 ? round(($successfulReferrals / $totalReferrals) * 100, 2) : 0;

        // Prepare data for Vue
        $referralData = $referrals->getCollection()->map(function ($ref) {
            $matchScore = 0;
            $matchDetails = [];

            $graduate = $ref->graduate;
            $job = $ref->job;

            if ($graduate && $job) {
                // Program match
                if ($graduate->program_id && $job->programs->pluck('id')->contains($graduate->program_id)) {
                    $matchScore += 20;
                    $matchDetails[] = 'Program Match';
                }
                // Skills match
                $graduateSkills = $graduate->graduateSkills->pluck('skill.name')->map(fn($s) => strtolower($s))->toArray();
                $jobSkills = collect(is_array($job->skills) ? $job->skills : json_decode($job->skills, true))->map(fn($s) => strtolower($s))->toArray();
                $skillsMatched = array_intersect($graduateSkills, $jobSkills);
                if (count($skillsMatched)) {
                    $matchScore += 40;
                    $matchDetails[] = 'Skills Match';
                }
                // Add more checks for experience, preferences, etc.
            }

            return [
                'id' => $ref->id,
                // Use graduate's full name instead of user->name
                'candidate' => $graduate
                    ? trim($graduate->first_name . ' ' . ($graduate->middle_name ? $graduate->middle_name . ' ' : '') . $graduate->last_name)
                    : 'N/A',
                'job_title' => $job ? $job->job_title : 'N/A',
                'company' => $job && $job->company ? $job->company->company_name : 'N/A',
                'status' => $ref->status,
                'referred_at' => $ref->created_at ? $ref->created_at->toDateString() : '',
                'hired_at' => $ref->status === 'hired' && $ref->updated_at ? $ref->updated_at->toDateString() : null,
                'match_score' => $matchScore,
                'match_details' => $matchDetails,
            ];
        });

        // Replace the paginator's collection with the mapped data
        $referrals->setCollection($referralData);

        return Inertia::render('Admin/ManageJobReferrals', [
            'referrals' => $referrals,
            'totalReferrals' => $totalReferrals,
            'successfulReferrals' => $successfulReferrals,
            'successRate' => $successRate,
            'search' => $request->search,
        ]);
    }


    public function generateCertificate(JobInvitation $referral)
    {
        // Fetch all needed data for the template
        $graduate = $referral->graduate;
        $job = $referral->job;
        $company = $job->company;



        // Return a view for the certificate (blade or PDF)
        return Inertia::render('Admin/ReferralCertificate', [
            'certificate' => [
                'date' => now()->format('M d, Y h:i A'),
                'graduate' => trim($graduate->first_name . ' ' . ($graduate->middle_name ? $graduate->middle_name . ' ' : '') . $graduate->last_name),
                'job_title' => $job->job_title,
                'company' => $company->company_name,
                'company_address' => $company->address ?? '',
                'photo' => $graduate->photo_url ?? '/default-photo.png', // adjust as needed
                'graduate_id' => $graduate->user_id,
            ]
        ]);
    }



    public function store(Request $request)
    {
        \Log::info('Request received', $request->all());

        try {
            $request->validate([
                'certificate' => 'required|file|mimes:pdf',
                'graduate_id' => 'required|integer|exists:users,id',
            ]);

            if (!$request->hasFile('certificate')) {
                \Log::error('No certificate file uploaded');
                return response()->json(['error' => 'No certificate file uploaded'], 400);
            }

            $path = $request->file('certificate')->store('private/certificates');
            \Log::info('Certificate stored at:', ['path' => $path]);

            $user = \App\Models\User::find($request->graduate_id);
            if ($user) {
                \Log::info('Notifying user with certificate path:', ['path' => $path]);
                $user->notify(new \App\Notifications\CertificateAvailable($path));
            }

            \App\Models\ReferralExport::create([
                'graduate_id' => $request->graduate_id,
                'job_invitation_id' => $request->job_invitation_id ?? null,
                'certificate_path' => $path,
            ]);

            return redirect()->back()->with('success', 'Certificate uploaded successfully.');
        } catch (\Exception $e) {
            \Log::error('Certificate store error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function download($filename)
    {
        $path = "private/certificates/{$filename}";
        if (!Storage::exists($path)) {
            abort(404);
        }
        return Storage::download($path);
    }
}
