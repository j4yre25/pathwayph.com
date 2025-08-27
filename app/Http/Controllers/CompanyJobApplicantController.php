<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\Job;
use App\Models\User;
use App\Models\JobApplication;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Services\ApplicantScreeningService;

class CompanyJobApplicantController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $company = Company::where('user_id', $user->id)->firstOrFail();
        $companyId = $company->id;

        // Get job IDs that have applicants
        $jobIdsWithApplicants = JobApplication::whereHas('job', fn($q) => $q->where('company_id', $companyId))
            ->pluck('job_id')
            ->unique()
            ->toArray();

        // Only show jobs with applicants
        $jobs = Job::where('company_id', $companyId)
            ->whereIn('id', $jobIdsWithApplicants)
            ->get();

        // Build base query for applicants
        $applicationsQuery = JobApplication::with([
            'graduate.user',
            'job.jobTypes',
            'job',
            'graduate.education',
            'graduate.experience',
            'graduate.graduateSkills.skill'
        ])->whereHas('job', fn($q) => $q->where('company_id', $companyId));

        // === Filters ===
        if ($request->filled('job_id')) {
            $applicationsQuery->where('job_id', $request->input('job_id'));
        }
        if ($request->filled('status')) {
            $applicationsQuery->where('status', $request->input('status'));
        }
        if ($request->filled('employment_type')) {
            $applicationsQuery->whereHas('job.jobTypes', function($q) use ($request) {
                $q->where('type', $request->input('employment_type'));
            });
        }
        if ($request->filled('date_from')) {
            $applicationsQuery->whereDate('applied_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $applicationsQuery->whereDate('applied_at', '<=', $request->input('date_to'));
        }
        if ($request->filled('stage')) {
            $applicationsQuery->where('stage', $request->input('stage'));
        }

        $screeningService = new ApplicantScreeningService();

        $applicants = $applicationsQuery->get()->map(function ($application) use ($screeningService) {
            $graduate = $application->graduate;
            $user = $graduate?->user;
            $job = $application->job;

            // Calculate match percentage using your service
            $match = $screeningService->screen($graduate, $job);

            return [
                'id' => $application->id,
                'name' => $graduate ? $graduate->first_name . ' ' . $graduate->last_name : 'N/A',
                'email' => $user?->email ?? 'N/A',
                'job_title' => $job?->job_title ?? '',
                'job_id' => $job?->id ?? '',
                'employment_type' => $job?->jobTypes?->map(fn($jt) => $jt->type)->join(', '),
                'status' => $application->status,
                'stage' => $application->stage,
                'applied_at' => optional($application->applied_at)->format('M d, Y'),
                'match_percentage' => $match['match_percentage'] ?? 0,
                'notes' => $application->notes ?? '',
                'profile_picture' => $graduate?->graduate_picture ?? null,
            ];
        });

        // KPI calculations
        $totalApplicants = $applicants->count();
        $hiredCount = $applicants->where('status', 'hired')->count();
        $rejectedCount = $applicants->where('status', 'rejected')->count();
        $interviewsCount = $applicants->where('status', 'interview')->count();

        // Average match %
        $averageMatch = $totalApplicants
            ? round($applicants->avg('match_percentage'))
            : 0;

        // This month label and count (optional)
        $thisMonthLabel = Carbon::now()->format('F Y');
        $thisMonthApplicants = $applicants->filter(function ($a) {
            return Carbon::parse($a['applied_at'])->isCurrentMonth();
        })->count();

        return Inertia::render('Company/Applicants/Index', [
            'applicants' => $applicants,
            'jobs' => $jobs->map(fn($j) => ['id' => $j->id, 'title' => $j->job_title]),
            'statuses' => ['shortlisted', 'under_review', 'not_recommended', 'applied', 'interview', 'hired', 'rejected'],
            'employmentTypes' => ['Full-time', 'Part-time', 'Internship', 'Contract', 'Freelance'],
            'filters' => $request->only(['job_id', 'status', 'employment_type', 'date_from', 'date_to', 'stage', 'match_range']),
            'stats' => [
                'total_applicants' => $totalApplicants,
                'hired' => $hiredCount,
                'rejected' => $rejectedCount,
                'interviews' => $interviewsCount,
                'average_match' => $averageMatch,
                'this_month' => $thisMonthApplicants,
                'this_month_label' => $thisMonthLabel,
            ],
        ]);
    }

    public function show(Job $job, Request $request)
    {
        
        $applicationsQuery = JobApplication::where('job_id', $job->id)
          ->with(['graduate.user', 'graduate.graduateSkills.skill', 'graduate.education', 'graduate.experience']);


        // === Search/Keyword filter ===
        if ($request->filled('keywords')) {
            $keywords = $request->input('keywords');
            $applicationsQuery->where(function ($q) use ($keywords) {
                $q->whereHas('graduate.user', function ($userQ) use ($keywords) {
                    $userQ->where('first_name', 'like', "%$keywords%")
                        ->orWhere('last_name', 'like', "%$keywords%")
                        ->orWhere('email', 'like', "%$keywords%");
                })
                ->orWhereHas('graduate.graduateSkills.skill', function ($skillQ) use ($keywords) {
                    $skillQ->where('name', 'like', "%$keywords%");
                })
                ->orWhereHas('graduate.education', function ($eduQ) use ($keywords) {
                    $eduQ->where('education', 'like', "%$keywords%");
                })
                ->orWhereHas('graduate.experience', function ($expQ) use ($keywords) {
                    $expQ->where('title', 'like', "%$keywords%");
                });
            });
        }

           // === Status filter ===
            if ($request->filled('status')) {
                $applicationsQuery->where('status', $request->input('status'));
            }

            // === Stage filter ===
            if ($request->filled('stage')) {
                $applicationsQuery->where('stage', $request->input('stage'));
            }

            // === Date range filter (applied_at) ===
            if ($request->filled('date_from')) {
                $applicationsQuery->whereDate('applied_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $applicationsQuery->whereDate('applied_at', '<=', $request->input('date_to'));
            }

            // === Stats (update as needed) ===
            $totalApplicants = $applicationsQuery->count();
            $hiredCount = (clone $applicationsQuery)->where('status', 'hired')->count();
            $rejectedCount = (clone $applicationsQuery)->where('status', 'rejected')->count();
            $declinedByGraduate = (clone $applicationsQuery)->where('status', 'declined')->count();
            $interviewsCount = (clone $applicationsQuery)->whereHas('interviews')->count();
            $pendingCount = $totalApplicants - ($hiredCount + $rejectedCount + $declinedByGraduate);

            $applicants = $applicationsQuery
                ->with(['graduate.user', 'job'])
                ->get()
                ->map(function ($application) {
                    $graduate = $application->graduate;
                    $user = $graduate?->user;

                    return [
                        'id' => $application->id,
                        'graduate_picture' => $graduate->graduate_picture,
                        'name' => $graduate
                            ? $graduate->first_name . ' ' . $graduate->last_name
                            : 'N/A',
                        'job_title' => $application->job->job_title,
                        'email' => $user?->email ?? 'N/A',
                        'status' => $application->status,
                        'stage' => $application->stage,
                        'notes' => $application->notes,
                        'applied_at' => optional($application->applied_at)->format('M d, Y'),
                        'interview_date' => optional($application->interviews->sortByDesc('scheduled_at')->first()?->scheduled_at)->format('M d, Y'),
                    ];
                });

            return Inertia::render('Company/Applicants/ListOfApplicants/Index', [
                'job' => $job,
                'applicants' => $applicants,
                'stats' => [
                    'total' => $totalApplicants,
                    'hired' => $hiredCount,
                    'rejected' => $rejectedCount,
                    'declined' => $declinedByGraduate,
                    'interviews' => $interviewsCount,
                    'pending' => $pendingCount,
                ],
                'filters' => $request->only(['keywords', 'status', 'stage', 'date_from', 'date_to']),
            ]);
        }

    public function autoScreen(Job $job)
    {
        $requiredDegree = 'Bachelor'; // Example
        $minExperience = 2; // years
        $requiredSkills = ['PHP', 'Vue.js']; // Example

        $applications = JobApplication::where('job_id', $job->id)
            ->with(['graduate.education', 'graduate.experience', 'graduate.graduateSkills.skill'])
            ->get();

        foreach ($applications as $application) {
            $graduate = $application->graduate;

            // Check degree
            $hasDegree = $graduate->education->contains(function($edu) use ($requiredDegree) {
                return stripos($edu->education, $requiredDegree) !== false;
            });

            // Check experience
            $hasExperience = $graduate->experience->sum(function($exp) {
                $start = $exp->start_date ? Carbon::parse($exp->start_date) : null;
                $end = $exp->end_date ? Carbon::parse($exp->end_date) : now();
                return $start && $end ? $start->diffInYears($end) : 0;
            }) >= $minExperience;

            // Check skills
            $skills = $graduate->graduateSkills->pluck('skill.name')->map(fn($s) => strtolower($s))->toArray();
            $hasSkills = collect($requiredSkills)->every(fn($skill) => in_array(strtolower($skill), $skills));

            // If all criteria met, move to screening
            if ($hasDegree && $hasExperience && $hasSkills) {
                $application->status = 'shortlisted';
                $application->stage = 'screening';
                $application->save();
            }
        }

        return back()->with('success', 'Automated screening completed!');
    }

}