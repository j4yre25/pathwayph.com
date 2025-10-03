<?php

namespace App\Http\Controllers;

use App\Models\JobInvitation;
use App\Models\Graduate;
use App\Models\Job;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Notification;
use App\Models\Referral;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ManageJobReferralsController extends Controller
{
    public function index(Request $request)
    {
        // Advanced filtering and search
        $query = \App\Models\Referral::with(['graduate.user', 'job.company', 'graduate.graduateSkills.skill', 'job.programs']);

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
        $successfulReferrals = $query->clone()->where('status', 'success')->count(); // <-- use 'success' status
        $successRate = $totalReferrals > 0 ? round(($successfulReferrals / $totalReferrals) * 100, 2) : 0;
        // Prepare data for Vue
       $referralData = $referrals->getCollection()->map(function ($ref) {
    $matchScore = 0;
    $matchDetails = [];
    $graduate = $ref->graduate;
    $job = $ref->job;

    // Define weights (keep consistent with recommendations)
    $weights = [
        'skills' => 3,
        'education' => 2,
        'experience' => 2,
        'job_type' => 1,
        'location' => 1,
        'work_environment' => 1,
        'min_salary' => 1,
        'max_salary' => 1,
        'salary_type' => 1,
        'keywords' => 2, // m10: search keywords
    ];
    $totalWeight = array_sum($weights);

    // All mi are binary (1 or 0)
    $mi = [
        'skills' => 0,
        'education' => 0,
        'experience' => 0,
        'job_type' => 0,
        'location' => 0,
        'work_environment' => 0,
        'min_salary' => 0,
        'max_salary' => 0,
        'salary_type' => 0,
        'keywords' => 0,
    ];

    if ($graduate && $job) {
        // m1: Skills
        $graduateSkills = $graduate->graduateSkills->pluck('skill.name')->filter()->unique()->toArray();
        $jobSkills = is_array($job->skills) ? $job->skills : (json_decode($job->skills, true) ?: []);
        foreach ($graduateSkills as $skill) {
            if (stripos(json_encode($jobSkills), $skill) !== false) {
                $mi['skills'] = 1;
                $matchDetails[] = 'Skills';
                break;
            }
        }

        // m2: Education
        $program = $graduate->program_id ? \App\Models\Program::find($graduate->program_id)->name ?? null : null;
        if ($program && stripos($job->job_requirements, $program) !== false) {
            $mi['education'] = 1;
            $matchDetails[] = 'Education';
        }

        // m3: Experience
        $experiences = $graduate->experience ? $graduate->experience->pluck('job_title')->filter()->unique()->toArray() : [];
        foreach ($experiences as $title) {
            if (stripos($job->job_title, $title) !== false) {
                $mi['experience'] = 1;
                $matchDetails[] = 'Experience';
                break;
            }
        }

        // m4: Preferred Job Type
        $preferences = $graduate->employmentPreference;
        $preferredJobTypes = $preferences && $preferences->job_type ? explode(',', $preferences->job_type) : [];
        if (in_array($job->job_type, $preferredJobTypes)) {
            $mi['job_type'] = 1;
            $matchDetails[] = 'Preferred Job Type';
        }

        // m5: Preferred Location
        $preferredLocations = $preferences && $preferences->location ? explode(',', $preferences->location) : [];
        if (in_array($job->location, $preferredLocations)) {
            $mi['location'] = 1;
            $matchDetails[] = 'Preferred Location';
        }

        // m6: Preferred Work Environment
        $preferredWorkEnvironments = $preferences && $preferences->work_environment ? explode(',', $preferences->work_environment) : [];
        if (in_array($job->work_environment, $preferredWorkEnvironments)) {
            $mi['work_environment'] = 1;
            $matchDetails[] = 'Preferred Work Environment';
        }

        // m7: Preferred Min Salary
        $minSalary = $preferences && $preferences->employment_min_salary ? $preferences->employment_min_salary : null;
        if ($minSalary && $job->job_min_salary >= $minSalary) {
            $mi['min_salary'] = 1;
            $matchDetails[] = 'Preferred Min Salary';
        }

        // m8: Preferred Max Salary
        $maxSalary = $preferences && $preferences->employment_max_salary ? $preferences->employment_max_salary : null;
        if ($maxSalary && $job->job_max_salary <= $maxSalary) {
            $mi['max_salary'] = 1;
            $matchDetails[] = 'Preferred Max Salary';
        }

        // m9: Preferred Salary Type
        $salaryType = $preferences && $preferences->salary_type ? $preferences->salary_type : null;
        if ($salaryType && stripos($job->job_salary_type, $salaryType) !== false) {
            $mi['salary_type'] = 1;
            $matchDetails[] = 'Preferred Salary Type';
        }

        // m10: Search Keywords (from job search history)
        $pastKeywords = $graduate->jobSearchHistory ? $graduate->jobSearchHistory->pluck('keywords')->unique()->toArray() : [];
        foreach ($pastKeywords as $keyword) {
            if (
                stripos($job->job_title, $keyword) !== false ||
                stripos($job->job_description, $keyword) !== false
            ) {
                $mi['keywords'] = 1;
                $matchDetails[] = 'Past Search';
                break;
            }
        }
    }

    // Calculate weighted score
    $score = 0;
    foreach ($weights as $key => $weight) {
        $score += $mi[$key] * $weight;
    }
    $match_percentage = $totalWeight > 0 ? round(($score / $totalWeight) * 100) : 0;

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
                'match_score' => $score,
                'match_details' => $matchDetails,
                'certificate_path' => $ref->certificate_path,
                'match_percentage' => $match_percentage,
            ];
        });

        // Replace the paginator's collection with the mapped data
        $referrals->setCollection($referralData);


        return Inertia::render('Admin/ManageJobReferrals', [
            'referrals' => $referrals,
            'totalReferrals' => $totalReferrals,
            'successfulReferrals' => $successfulReferrals,
            'successRate' => $successRate,
            'filters' => [
                'status' => $request->status,
                'company' => $request->company,
                'candidate' => $request->candidate,
                'search' => $request->search,
            ],
        ]);
    }


    public function generateCertificate(Referral $referral)
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
                'referral_id' => $referral->id, // <-- ADD THIS

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

            $path = $request->file('certificate')->store('certificates', 'private');
            \Log::info('Certificate stored at:', ['path' => $path]);

            $user = \App\Models\User::find($request->graduate_id);
            if ($user) {
                \Log::info('Notifying user with certificate path:', ['path' => $path]);
                $user->notify(new \App\Notifications\CertificateAvailable($path));
            }

            $referral = \App\Models\Referral::find($request->referral_id);
            if ($referral) {
                $referral->certificate_path = $path; // e.g. 'certificates/filename.pdf'
                $referral->status = 'success';
                $referral->save();
            }

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

    public function downloadCertificate(Referral $referral)
    {
        if (!$referral->certificate_path || !Storage::exists($referral->certificate_path)) {
            abort(404);
        }
        // Optionally, add authorization here if needed
        return Storage::download($referral->certificate_path);
    }

    public function viewCertificate(Referral $referral)
    {
        if (!$referral->certificate_path || !\Storage::disk('private')->exists($referral->certificate_path)) {
            abort(404);
        }
        $mime = \Storage::disk('private')->mimeType($referral->certificate_path);
        $headers = [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($referral->certificate_path) . '"'
        ];
        return response()->file(
            storage_path('app/private/' . $referral->certificate_path),
            $headers
        );
    }


    public function markSuccess(Referral $referral)
    {
        $referral->status = 'success';
        $referral->save();



        return response()->json(['success' => true]);
    }

    public function decline(Referral $referral)
    {
        $referral->status = 'rejected';
        $referral->save();

        return response()->json(['success' => true]);
    }
}
