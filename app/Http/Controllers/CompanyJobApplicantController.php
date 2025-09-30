<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\Job;
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
            $this->abortIfOnHold(auth()->user());

        $user = Auth::user();
        $company = Company::where('user_id', $user->id)->firstOrFail();
        $companyId = $company->id;

        $jobIdsWithApplicants = JobApplication::whereHas('job', fn($q) => $q->where('company_id', $companyId))
            ->pluck('job_id')
            ->unique()
            ->toArray();

        $jobs = Job::where('company_id', $companyId)
            ->whereIn('id', $jobIdsWithApplicants)
            ->get()
            ->map(fn($j) => ['id' => $j->id, 'job_title' => $j->job_title])
            ->values(); // ensure plain array

        $applicationsQuery = JobApplication::with([
            'graduate.user',
            'job.jobTypes',
            'job',
            'graduate.education',
            'graduate.experience',
            'graduate.graduateSkills.skill'
        ])->whereHas('job', fn($q) => $q->where('company_id', $companyId));

        // Filters
        if ($request->filled('job_id')) {
            $applicationsQuery->where('job_id', $request->input('job_id'));
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
            // stage stored as canonical slug lowercase
            $applicationsQuery->whereRaw('LOWER(stage) = ?', [strtolower($request->input('stage'))]);
        }

        $screeningService = new ApplicantScreeningService();

        $applicants = $applicationsQuery->get()->map(function ($application) use ($screeningService) {
            $graduate = $application->graduate;
            $user = $graduate?->user;
            $job = $application->job;

            // If DB has match_percentage & screening_label, prefer them; else compute
            $matchPercentage = $application->match_percentage;
            $screeningLabel = $application->screening_label;

            if ($matchPercentage === null || !$screeningLabel) {
                $match = $screeningService->screen($graduate, $job);
                $matchPercentage = $match['match_percentage'] ?? 0;
                $screeningLabel = $match['screening_label'] ?? null;
            }

            // Normalize stage to slug
            $stageSlug = strtolower($application->stage ?? 'applied');

            return [
                'id' => $application->id,
                'name' => $graduate ? $graduate->first_name . ' ' . $graduate->last_name : 'N/A',
                'email' => $user?->email ?? 'N/A',
                'job_title' => $job?->job_title ?? '',
                'job_id' => $job?->id ?? '',
                'employment_type' => $job?->jobTypes?->map(fn($jt) => $jt->type)->join(', '),
                'status' => $application->status,
                'stage' => $stageSlug,
                'screening_label' => $screeningLabel,
                'applied_at' => optional($application->applied_at)->format('M d, Y'),
                'applied_at_iso' => optional($application->applied_at)->format('Y-m-d'),
                'match_percentage' => (int) $matchPercentage,
                'notes' => $application->notes ?? '',
                'profile_picture' => $graduate?->graduate_picture ?? null,
            ];
        })->values(); // ensure plain array

        $totalApplicants = $applicants->count();
        $hiredCount = $applicants->where('status', 'hired')->count();
        $rejectedCount = $applicants->where('status', 'rejected')->count();
        $interviewsCount = $applicants->where('stage', 'interview')->count();
        $averageMatch = $totalApplicants ? round($applicants->avg('match_percentage')) : 0;
        $thisMonthLabel = Carbon::now()->format('F Y');
        $thisMonthApplicants = $applicants->filter(fn($a) => Carbon::parse($a['applied_at_iso'])->isCurrentMonth())->count();

        return Inertia::render('Company/Applicants/Index', [
            'applicants' => $applicants,
            'jobs' => $jobs,
            'employmentTypes' => ['Full-time','Part-time','Internship','Contract','Freelance'],
            'filters' => $request->only([
                'job_id','employment_type','date_from','date_to','stage','match_range','screening_label','selected_applied_date'
            ]),
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

    protected function abortIfOnHold($user = null)
{
    $user = $user ?: auth()->user();
    if ($user->status === 'on_hold') {
        abort(403, 'Your company account is currently on hold. Please contact PESO for assistance.');
    }
}


}

