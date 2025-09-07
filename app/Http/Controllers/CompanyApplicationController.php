<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\Interview;
use App\Notifications\InterviewScheduledNotification;
use App\Notifications\ApplicationStatusUpdated;
use App\Models\JobOffer;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\JobPipelineStage;
use App\Models\JobApplicationStageLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class CompanyApplicationController extends Controller
{
/**
 * Display the specified graduate's portfolio.
 *
 * @param  \App\Models\User  $user
 * @return \Inertia\Response
 */
    public function show(JobApplication $application)
    {
            // Load related data (skills, portfolio, etc.)
        $application->load([
                'graduate.user',       // Basic user info
                'graduate.graduateSkills.skill',    
                'graduate.education',   
                'graduate.experience',
                'graduate.projects',
                'graduate.achievements',
                'graduate.certifications',
                'graduate.testimonials',
                'graduate.employmentPreference',
                'graduate.employmentPreference.locations',
                'graduate.employmentPreference.salary',
                'graduate.careerGoals',
                'graduate.resume',
                'job',                 // Job applied for
                'graduate.referralExports', // <-- Added relation
            ]);

        $graduate = $application->graduate;

        $resume = $graduate->resume;
        if ($resume) {
            $resume->file_url = Storage::url('resumes/' . $resume->file_name); // or $resume->file_path
        }

         // ===== Employment Preference Normalization (handles array / json / csv / relations) =====
        $ep = $graduate?->employmentPreference;
        $employmentPreferencesTransformed = null;

        $decodeMulti = function($raw) {
            if (is_null($raw) || $raw === '') return [];
            if (is_array($raw)) return array_values(array_filter(array_map('trim', $raw)));
            if (is_string($raw)) {
                $t = trim($raw);
                // Try JSON
                if ((str_starts_with($t,'[') && str_ends_with($t,']')) ||
                    (str_starts_with($t,'{') && str_ends_with($t,'}'))) {
                    $decoded = json_decode($t, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        if (is_array($decoded)) {
                            // If list of scalars or objects
                            return array_values(array_filter(array_map(function($v){
                                if (is_array($v) && isset($v['name'])) return trim($v['name']);
                                if (is_string($v)) return trim($v);
                                return null;
                            }, $decoded)));
                        }
                    }
                }
                // CSV
                return array_values(array_filter(array_map('trim', explode(',',$raw))));
            }
            return [];
        };

        if ($ep) {
            // Job Types (relations OR raw column)
            if ($ep->relationLoaded('jobTypes') && $ep->jobTypes?->count()) {
                $jobTypes = $ep->jobTypes->pluck('name')->filter()->values();
            } else {
                $jobTypes = collect(
                    $decodeMulti($ep->job_type ?? $ep->getRawOriginal('job_type'))
                );
            }

            // Work Environments (relation OR work_environment / work_enviroment column)
            if ($ep->relationLoaded('workEnvironments') && $ep->workEnvironments?->count()) {
                $workEnvironments = $ep->workEnvironments->pluck('name')->filter()->values();
            } else {
                $workEnvironments = collect(
                    $decodeMulti(
                        $ep->work_environment ??
                        $ep->getRawOriginal('work_environment')
                    )
                );
            }

            // Locations (relation OR fallback)
            if ($ep->relationLoaded('locations') && $ep->locations?->count()) {
                $locations = $ep->locations->map(fn($loc) => [
                    'name' => $loc->name ?? null,
                    'address' => $loc->address ?? ($loc->name ?? null),
                ])->values();
            } else {
                $locationsRaw = $decodeMulti($ep->location ?? $ep->getRawOriginal('location'));
                $locations = collect($locationsRaw)->map(fn($v) => [
                    'name' => $v,
                    'address' => $v,
                ])->values();
            }

            // Salary
            $minSalary = $ep->employment_min_salary ?? ($ep->salary->min_salary ?? null);
            $maxSalary = $ep->employment_max_salary ?? ($ep->salary->max_salary ?? null);

            $employmentPreferencesTransformed = [
                'employment_min_salary' => $minSalary,
                'employment_max_salary' => $maxSalary,
                'job_types' => $jobTypes->map(fn($n) => ['name'=>$n])->values(),
                'work_environments' => $workEnvironments->map(fn($n) => ['name'=>$n])->values(),
                'locations' => $locations,
                'additional_notes' => $ep->additional_notes ?? $ep->notes ?? null,
            ];
        }

        $referralCertificates = $graduate?->referralExports?->map(function ($r) {
            $path = $r->certificate_path;
            // If already a full URL leave it, else build storage URL
            $url = str_starts_with($path, 'http')
                ? $path
                : (\Storage::exists($path) ? \Storage::url($path) : null);
            return [
                'id' => $r->id,
                'file_name' => basename($path),
                'file_url' => $url,
                'raw_path' => $path,
                'uploaded_at' => $r->created_at,
            ];
        })?->values() ?? [];

        return Inertia::render('Company/Applicants/ListOfApplicants/ApplicantProfile', [
            'applicant' => $application,
            'graduate' => $graduate,
            'skills' => $graduate?->graduateSkills?->map(function($gs) {
                $skill = $gs->skill;
                // Collect possible type/category sources
                $candidates = [
                    $gs->type           ?? null,
                    
                ];
                $groupLabel = collect($candidates)
                    ->filter(fn($v) => $v && is_string($v))
                    ->map(fn($v) => trim($v))
                    ->first() ?? 'Uncategorized';

                return [
                    'id' => $gs->id,
                    'skill_id' => $gs->skill_id,
                    'skill_name' => $skill->name ?? null,
                    'years_experience' => $gs->years_experience,
                    'proficiency_type' => $gs->proficiency_type,
                    'type' => $skill->type ?? null,
                    'category' => $skill->category ?? null,
                    'skill_type' => $skill->skill_type ?? null,
                    'group' => $skill->group ?? null,
                    'group_label' => $groupLabel,
                ];
            }) ?? [],
            'experiences' => $graduate?->experience ?? [],
            // FIX: provide an array for education (previous line was broken)
            'education' => $graduate?->education?->map(function($e){
                return [
                    'id' => $e->id,
                    'education' => $e->education ?? $e->degree ?? $e->degree_level ?? $e->level,
                    'program' => $e->program ?? $e->field_of_study,
                    'field_of_study' => $e->field_of_study ?? $e->program,
                    'institution' => $e->institution ?? $e->school,
                    'graduation_year' => $e->graduation_year ?? $e->end_year,
                    'start_date' => $e->start_date,
                    'end_date' => $e->end_date,
                    'school_year' => $e->school_year,
                ];
            })?->values() ?? [],
            'projects' => $graduate?->projects ?? [],
            'achievements' => $graduate?->achievements ?? [],
            'certifications' => $graduate?->certifications ?? [],
            'testimonials' => $graduate?->testimonials ?? [],
            'employmentPreferences' => $employmentPreferencesTransformed,
            'careerGoals' => $graduate?->careerGoals,
            'resume' => ($resume = $graduate?->resume) ? [
                'file_url' => Storage::url($resume->file_path),
                'file_type' => $resume->file_type,
                'file_name' => $resume->file_name,
            ] : null,
            'job' => $application->job,
            'referralCertificates' => $referralCertificates, // <-- Added referralCertificates
        ]);
    }

    public function update(Request $request, JobApplication $application)
    {
        $data = $request->validate([
            'notes' => 'sometimes|nullable|string|max:2000',
            'job_pipeline_stage_id' => 'sometimes|nullable|exists:job_pipeline_stages,id',
        ]);

        $changed = [];

        if (array_key_exists('notes',$data) && $data['notes'] !== $application->notes) {
            $application->notes = $data['notes'];
            $changed[] = 'notes';
        }

        if (array_key_exists('job_pipeline_stage_id',$data)
            && (int)$data['job_pipeline_stage_id'] !== (int)$application->job_pipeline_stage_id) {

            $fromId = $application->job_pipeline_stage_id;
            $stage = JobPipelineStage::find($data['job_pipeline_stage_id']);

            if ($stage) {
                $application->job_pipeline_stage_id = $stage->id;
                // keep legacy string column aligned
                $application->stage = $stage->slug;
                $changed[] = 'stage';

                JobApplicationStageLog::create([
                    'job_application_id' => $application->id,
                    'from_stage_id'      => $fromId,
                    'to_stage_id'        => $stage->id,
                    'changed_by'         => Auth::id(),
                    'changed_at'         => Carbon::now(),
                ]);
            }
        }

        if (in_array('stage', $changed) && method_exists($application,'syncStatusFromStage')) {
            if ($application->syncStatusFromStage() && !in_array('status',$changed)) {
                $changed[] = 'status';
            }
        }

        if ($changed) $application->save();

        return back()->with('success', 'Updated: '.implode(', ',$changed));
    }

    public function scheduleInterview(Request $request, JobApplication $application)
    {
        $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'location' => 'nullable|string|max:255',
        ]);

        $interview = Interview::create([
            'job_application_id' => $application->id,
            'scheduled_at' => $request->scheduled_at,
            'location' => $request->location,
        ]);

        $interview->load('jobApplication.job.company');
        
        // Send notification to graduate/user
        if ($application->graduate && $application->graduate->user) {
        $application->graduate->user->notify(new InterviewScheduledNotification($interview));
        }


        // (Optional) Integrate with Google/Outlook Calendar here and save event ID

        return redirect()->back()->with('success', 'Interview scheduled and notification sent.');
    }

    public function storeOffer(Request $request, JobApplication $application)
    {
        $request->validate([
            'offered_salary' => 'required|numeric|min:0',
            'start_date' => 'required|date|after:today',
            'offer_letter' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $offerData = [
            'job_application_id' => $application->id,
            'offered_salary' => $request->offered_salary,
            'start_date' => $request->start_date,
        ];

        if ($request->hasFile('offer_letter')) {
            $offerData['offer_letter_path'] = $request->file('offer_letter')->store('offer_letters', 'public');
        }

        $offer = JobOffer::create($offerData);

        // Update application status
        $application->status = 'offer_sent';
        $application->save();

        // Notify applicant
        if ($application->graduate && $application->graduate->user) {
            $application->graduate->user->notify(new ApplicationStatusUpdated($application, $application->status));
        }

        return redirect()->back()->with('success', 'Job offer sent successfully.');
    }

    public function updateStatus(Request $request, JobApplication $application)
    {
        $request->validate([
            'status' => 'required|string|in:under_review,offer_sent,offer_accepted,offer_declined,hired'
        ]);

        $application->status = $request->status;

        if (method_exists($application,'syncStatusFromStage')) {
            $application->syncStatusFromStage(); // will override if stage dictates
        }

        $application->save();

        // If hired, update graduate's info
        if ($request->status === 'hired') {
            $graduate = $application->graduate;
            if ($graduate && $application->job) {
                $graduate->current_job_title = $application->job->job_title ?? 'Hired';
                $graduate->company_id = $application->job->company_id;
                $graduate->employment_status = 'Employed';
                $graduate->save();
            }
        }

        // Notify applicant
        if ($application->graduate && $application->graduate->user) {
            $application->graduate->user->notify(new ApplicationStatusUpdated($application, $application->status));
        }

        return back()->with('success', 'Status updated.');
    }
}
