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
            $criteria = 0;
            $graduate = $ref->graduate;
            $job = $ref->job;

            if ($graduate && $job) {
                // Skills
                $criteria++;
                $graduateSkills = $graduate->graduateSkills->pluck('skill.name')->filter()->unique()->toArray();
                $jobSkills = is_array($job->skills) ? $job->skills : (json_decode($job->skills, true) ?: []);
                $skillMatch = false;
                foreach ($graduateSkills as $skill) {
                    if (stripos(json_encode($jobSkills), $skill) !== false) {
                        $matchDetails[] = 'Skills';
                        $skillMatch = true;
                        break;
                    }
                }
                if ($skillMatch) $matchScore++;

                // Education (Program)
                $criteria++;
                $program = $graduate->program_id ? \App\Models\Program::find($graduate->program_id)->name ?? null : null;
                $educationMatch = $program && stripos($job->job_requirements, $program) !== false;
                if ($educationMatch) {
                    $matchDetails[] = 'Education';
                    $matchScore++;
                }

                // Experience
                $criteria++;
                $experiences = $graduate->experience ? $graduate->experience->pluck('job_title')->filter()->unique()->toArray() : [];
                $experienceMatch = false;
                foreach ($experiences as $title) {
                    if (stripos($job->job_title, $title) !== false) {
                        $matchDetails[] = 'Experience';
                        $experienceMatch = true;
                        break;
                    }
                }
                if ($experienceMatch) $matchScore++;

                // Preferred Job Type
                $criteria++;
                $preferences = $graduate->employmentPreference;
                $preferredJobTypes = $preferences && $preferences->job_type ? explode(',', $preferences->job_type) : [];
                $jobTypeMatch = in_array($job->job_type, $preferredJobTypes);
                if ($jobTypeMatch) {
                    $matchDetails[] = 'Preferred Job Type';
                    $matchScore++;
                }

                // Preferred Location
                $criteria++;
                $preferredLocations = $preferences && $preferences->location ? explode(',', $preferences->location) : [];
                $locationMatch = in_array($job->location, $preferredLocations);
                if ($locationMatch) {
                    $matchDetails[] = 'Preferred Location';
                    $matchScore++;
                }

                // Preferred Work Environment
                $criteria++;
                $preferredWorkEnvironments = $preferences && $preferences->work_environment ? explode(',', $preferences->work_environment) : [];
                $workEnvMatch = in_array($job->work_environment, $preferredWorkEnvironments);
                if ($workEnvMatch) {
                    $matchDetails[] = 'Preferred Work Environment';
                    $matchScore++;
                }

                // Preferred Min Salary
                $criteria++;
                $minSalary = $preferences && $preferences->employment_min_salary ? $preferences->employment_min_salary : null;
                $minSalaryMatch = $minSalary && $job->job_min_salary >= $minSalary;
                if ($minSalaryMatch) {
                    $matchDetails[] = 'Preferred Min Salary';
                    $matchScore++;
                }

                // Preferred Max Salary
                $criteria++;
                $maxSalary = $preferences && $preferences->employment_max_salary ? $preferences->employment_max_salary : null;
                $maxSalaryMatch = $maxSalary && $job->job_max_salary <= $maxSalary;
                if ($maxSalaryMatch) {
                    $matchDetails[] = 'Preferred Max Salary';
                    $matchScore++;
                }

                // Preferred Salary Type
                $criteria++;
                $salaryType = $preferences && $preferences->salary_type ? $preferences->salary_type : null;
                $salaryTypeMatch = $salaryType && stripos($job->job_salary_type, $salaryType) !== false;
                if ($salaryTypeMatch) {
                    $matchDetails[] = 'Preferred Salary Type';
                    $matchScore++;
                }

                // Past Search Keywords
                $criteria++;
                $pastKeywords = $graduate->jobSearchHistory ? $graduate->jobSearchHistory->pluck('keywords')->unique()->toArray() : [];
                $pastKeywordMatch = false;
                foreach ($pastKeywords as $keyword) {
                    if (
                        stripos($job->job_title, $keyword) !== false ||
                        stripos($job->job_description, $keyword) !== false
                    ) {
                        $matchDetails[] = 'Past Search';
                        $pastKeywordMatch = true;
                        break;
                    }
                }
                if ($pastKeywordMatch) $matchScore++;
            }
            $match_percentage = $criteria > 0 ? round(($matchScore / $criteria) * 100) : 0;

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
