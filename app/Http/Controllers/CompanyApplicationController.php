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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Messages;
use App\Models\User;
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

private function normalizePayload($payload): array
    {
        if (is_array($payload)) return $payload;
        if (is_object($payload)) return (array) $payload;
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }

    // Helper: try to extract a Carbon date-time from a payload with common keys
    private function extractDateTimeFromPayload(array $p): ?Carbon
    {
        $candidates = [
            'scheduled_at','schedule_at','interview_at','interview_date','date','datetime','rescheduled_at','new_date'
        ];
        foreach ($candidates as $k) {
            if (!empty($p[$k])) {
                try { return Carbon::parse($p[$k]); } catch (\Throwable $e) {}
            }
        }
        // Some forms store separate date/time
        if (!empty($p['date']) && !empty($p['time'])) {
            try { return Carbon::parse($p['date'].' '.$p['time']); } catch (\Throwable $e) {}
        }
        if (!empty($p['day']) && !empty($p['time'])) {
            try { return Carbon::parse($p['day'].' '.$p['time']); } catch (\Throwable $e) {}
        }
        return null;
    }
    private function resolveStageName(object $row, string $prefix, array $byId, array $bySlug): string
    {
        $pid   = "{$prefix}_stage_id";
        $pslug = "{$prefix}_stage_slug";
        $pname = "{$prefix}_stage";

        if (property_exists($row, $pid) && !is_null($row->{$pid})) {
            return $byId[$row->{$pid}] ?? '—';
        }
        if (property_exists($row, $pslug) && !is_null($row->{$pslug})) {
            return $bySlug[$row->{$pslug}] ?? ucfirst(str_replace('_',' ', (string)$row->{$pslug}));
        }
        if (property_exists($row, $pname) && !is_null($row->{$pname})) {
            return (string)$row->{$pname};
        }
        return '—';
    }

    private function userDisplayName($userId): ?string
    {
        if (!$userId) return null;

        // Prefer HR full name
        $hrName = DB::table('human_resources')
            ->where('user_id', $userId)
            ->selectRaw("TRIM(CONCAT(COALESCE(first_name,''),' ',COALESCE(last_name,''))) as name")
            ->value('name');
        if ($hrName && trim($hrName) !== '') return $hrName;

        // Try Graduate profile
        if (Schema::hasTable('graduates')) {
            $gradName = DB::table('graduates')
                ->where('user_id', $userId)
                ->selectRaw("TRIM(CONCAT(COALESCE(first_name,''),' ',COALESCE(last_name,''))) as name")
                ->value('name');
            if ($gradName && trim($gradName) !== '') return $gradName;
        }

        // Fallback to email
        return DB::table('users')->where('id', $userId)->value('email');
    }

    public function show(JobApplication $application)
    {
            // Load related data (skills, portfolio, etc.)
        $application->load([
                'graduate.user',       
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
                'job',                 
                'graduate.referrals', 
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

        // Ensure referrals relation loaded
        $graduate?->loadMissing('referrals');

        $referralCertificates = collect();

        if ($graduate && $graduate->referrals?->count()) {
            $referralCertificates = $graduate->referrals
                ->whereNotNull('certificate_path')
                ->filter(fn($r) => trim($r->certificate_path) !== '')
                ->map(function ($r) {
                    $path = $r->certificate_path;
                    // If stored under private/ generate a download route (make sure route name exists)
                    $isPrivate = str_starts_with($path, 'private/');
                    $fileName = basename($path);
                    $fileUrl = null;

                    if ($isPrivate) {
                        if (function_exists('route')) {
                            try {
                                $fileUrl = route('referrals.certificates.download', ['filename' => $fileName]);
                            } catch (\Throwable $e) {
                                $fileUrl = null;
                            }
                        }
                    } else {
                        if (\Storage::exists($path)) {
                            $fileUrl = \Storage::url($path);
                        }
                    }

                    return [
                        'id' => $r->id,
                        'file_name' => $fileName,
                        'file_url' => $fileUrl,
                        'raw_path' => $path,
                        'uploaded_at' => $r->created_at,
                    ];
                })
                ->values();
        }

        $educationRank = [
            'phd' => 1,'doctor'=>'1','doctorate'=>1,
            "master's"=>2,'masters'=>2,'master'=>2,
            "bachelor's"=>3,'bachelors'=>3,'bachelor'=>3,
            'associate'=>4,'certificate'=>5,
            'vocational'=>6,
            'senior high'=>7,'high school'=>7,
        ];
        $norm = fn($v) => strtolower(trim($v ?? ''));

        $educationCollection = $graduate?->education ?? collect();

        $education = $educationCollection->map(function($e){
            return [
                'id' => $e->id,
                'school_name' => $e->school_name,
                'program' => $e->program,
                'level_of_education' => $e->level_of_education,
                'start_date' => $e->start_date,
                'end_date' => $e->is_current ? null : $e->end_date,
                'is_current' => (bool)$e->is_current,
                'description' => $e->description,
                'achievement' => $e->achievement,
                'deleted_at' => $e->deleted_at,
            ];
        })->values();

        $highestEducation = $education
            ->sort(function($a,$b) use($educationRank,$norm){
                $ra = $educationRank[$norm($a['level_of_education'])] ?? 999;
                $rb = $educationRank[$norm($b['level_of_education'])] ?? 999;
                if ($ra !== $rb) return $ra <=> $rb;
                // current first
                if ($a['is_current'] && !$b['is_current']) return -1;
                if (!$a['is_current'] && $b['is_current']) return 1;
                // most recent end (or start) date
                $aDate = $a['end_date'] ?? $a['start_date'] ?? '0000-00-00';
                $bDate = $b['end_date'] ?? $b['start_date'] ?? '0000-00-00';
                return strcmp($bDate,$aDate);
            })
            ->first();

        // Build Stage Activities
        $activities = collect();
        
        $applicantName = trim(($graduate->first_name ?? '') . ' ' . ($graduate->last_name ?? ''));
        $jobTitle = $application->job->job_title ?? 'the position';
        $activities->push([
            'type'  => 'action',
            'stage' => 'applied',
            'by'    => $applicantName ?: 'Applicant',
            'at'    => $application->created_at,
            'text'  => " applied for {$jobTitle}",
            'meta'  => null,
        ]);
        // Stage change logs
         if (Schema::hasTable('job_application_stage_logs')) {
            // Maps for both id->name and slug->name
            $stageById   = DB::table('job_pipeline_stages')->pluck('name','id')->toArray();
            $stageBySlug = DB::table('job_pipeline_stages')->pluck('name','slug')->toArray();

            $hasChangedAt = Schema::hasColumn('job_application_stage_logs', 'changed_at');

            $q = DB::table('job_application_stage_logs')
                ->where('job_application_id', $application->id);
            if ($hasChangedAt) $q->orderByDesc('changed_at');
            $q->orderByDesc('created_at');

            $stageLogs = $q->get();

            $nl = fn($v) => strtolower(trim((string)$v));
            $autoScreenMsgAdded = false;

            // Try to find a match percentage on the application record
            $matchPercent = $application->match_percentage
                ?? $application->matching_percentage
                ?? $application->match_percent
                ?? $application->match_score
                ?? $application->score
                ?? null;
            if (is_numeric($matchPercent)) $matchPercent = (int) round($matchPercent);

            foreach ($stageLogs as $log) {
                $byName   = $this->userDisplayName($log->changed_by ?? null);
                $fromName = $this->resolveStageName($log, 'from', $stageById, $stageBySlug);
                $toName   = $this->resolveStageName($log, 'to',   $stageById, $stageBySlug);

                $fromL = $nl($fromName);
                $toL   = $nl($toName);

                $isInitialToApplying   = ($fromL === '' || $fromL === '—' || $fromL === '-') && ($toL === 'applying' || $toL === 'applied');
                $isApplyingToScreening = ($fromL === 'applying' || $fromL === 'applied' || $fromL === '') && $toL === 'screening';
                $endsAtApplying        = ($toL === 'applying' || $toL === 'applied');

                if ($isInitialToApplying || $isApplyingToScreening || $endsAtApplying) {
                    // Instead of "applying -> screening", optionally add an auto-screened note once
                    if ($isApplyingToScreening && !$autoScreenMsgAdded) {
                        $autoText = $applicantName
                            ? " automatically screened"
                            : "Applicant automatically screened";
                        if ($matchPercent !== null) {
                            $autoText .= ", having {$matchPercent}% match for the {$jobTitle}";
                        }
                        $activities->push([
                            'type'  => 'action',
                            'stage' => 'screening',
                            'by'    => $applicantName ?: 'Applicant',
                            'at'    => ($hasChangedAt && !empty($log->changed_at)) ? $log->changed_at : $log->created_at,
                            'text'  => $autoText,
                            'meta'  => null,
                        ]);
                        $autoScreenMsgAdded = true;
                    }
                    continue; // do not add the skipped stage-change item
                }

                $activities->push([
                    'type'  => 'stage_change',
                    'stage' => strtolower($toName),
                    'by'    => $byName,
                    'at'    => ($hasChangedAt && !empty($log->changed_at)) ? $log->changed_at : $log->created_at,
                    // CHANGED: clearer sentence
                    'text'  => "moved the applicant from {$fromName} to {$toName}",
                    'meta'  => null,
                ]);
            }
        }

        // Action logs (request info, interview schedule, etc.)
        if (Schema::hasTable('job_application_action_logs')) {
            $actionLogs = DB::table('job_application_action_logs')
                ->where('job_application_id', $application->id)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($actionLogs as $log) {
                $userName = $this->userDisplayName($log->user_id ?? null);
                $payload  = $this->normalizePayload($log->payload ?? []);
                $key      = (string)($log->action_key ?? '');

                // Build human text per action
                $text = 'performed an action';
                $stageForAction = strtolower($application->stage ?? 'applied');

                switch ($key) {
                    case 'request_info':
                    case 'request_more_info':
                        $req = [];
                        if (!empty($payload['requested']) && is_array($payload['requested'])) {
                            $req = array_filter(array_map('trim', $payload['requested']));
                        } elseif (!empty($payload['type'])) {
                            $req = [(string)$payload['type']];
                        }
                        $extra = $req ? ': '.implode(', ', $req) : '';
                        $text = "requested more info{$extra}";
                        break;

                    case 'schedule_interview':
                        $dt = $this->extractDateTimeFromPayload($payload);
                        $when = $dt ? (' on '.$dt->format('M d, Y').' at '.$dt->format('h:i A')) : '';
                        $loc  = !empty($payload['location']) ? ' ('.$payload['location'].')' : '';
                        $text = "scheduled an interview{$when}{$loc}";
                        $stageForAction = 'interview';
                        break;

                    case 'reschedule_interview':
                        $dt = $this->extractDateTimeFromPayload($payload);
                        $when = $dt ? (' to '.$dt->format('M d, Y').' at '.$dt->format('h:i A')) : '';
                        $loc  = !empty($payload['location']) ? ' ('.$payload['location'].')' : '';
                        $text = "rescheduled the interview{$when}{$loc}";
                        $stageForAction = 'interview';
                        break;

                    case 'record_interview_feedback':
                        $rec = $payload['recommendation'] ?? $payload['status'] ?? null;
                        $summary = $payload['feedback'] ?? $payload['notes'] ?? null;
                        $text = "recorded interview feedback".($rec ? " - {$rec}" : '');
                        if ($summary) $text .= ": ".$summary;
                        $stageForAction = 'interview';
                        break;

                    case 'send_exam_instructions':
                        $dt = $this->extractDateTimeFromPayload($payload);
                        $when = $dt ? (' for '.$dt->format('M d, Y').' '.$dt->format('h:i A')) : '';
                        $text = "sent exam instructions{$when}";
                        $stageForAction = 'assessment';
                        break;

                    case 'record_test_results':
                        $score = $payload['score'] ?? $payload['result'] ?? $payload['summary'] ?? null;
                        $text = "recorded test results".($score ? ": {$score}" : '');
                        $stageForAction = 'assessment';
                        break;

                    case 'reschedule_test':
                        $dt = $this->extractDateTimeFromPayload($payload);
                        $when = $dt ? (' to '.$dt->format('M d, Y').' at '.$dt->format('h:i A')) : '';
                        $text = "rescheduled the exam{$when}";
                        $stageForAction = 'assessment';
                        break;

                    case 'send_offer':
                        $salary = $payload['offered_salary'] ?? $payload['salary'] ?? null;
                        $start  = !empty($payload['start_date']) ? Carbon::parse($payload['start_date'])->format('M d, Y') : null;
                        $extra  = [];
                        if ($salary !== null) $extra[] = "salary {$salary}";
                        if ($start) $extra[] = "start date {$start}";
                        $tail = $extra ? ' ('.implode(', ', $extra).')' : '';
                        $text = "sent an offer{$tail}";
                        $stageForAction = 'offer';
                        break;

                    case 'hire':
                        $text = "marked the applicant as hired";
                        $stageForAction = 'hired';
                        break;

                    case 'reject':
                    case 'reject_withdraw':
                        $reason = $payload['reason'] ?? null;
                        $text = "rejected the applicant".($reason ? ": {$reason}" : '');
                        $stageForAction = 'rejected';
                        break;

                    case 'add_remark':
                    case 'note_added':
                        $remark = trim((string)($payload ?: ''));
                        $remark = $remark === '' && !empty($payload['remark']) ? $payload['remark'] : $remark;
                        $text = "added a remark".($remark ? ": ".$remark : '');
                        break;

                    default:
                        // Fallback to generic label if we had one
                        $labelMap = [
                            'request_info' => 'requested more info',
                            'request_more_info' => 'requested more info',
                            'schedule_interview' => 'scheduled an interview',
                            'reschedule_interview' => 'rescheduled interview',
                            'record_interview_feedback' => 'recorded interview feedback',
                            'send_exam_instructions' => 'sent exam instructions',
                            'record_test_results' => 'recorded test results',
                            'reschedule_test' => 'rescheduled test',
                            'send_offer' => 'sent an offer',
                            'hire' => 'marked as hired',
                            'reject' => 'rejected applicant',
                            'reject_withdraw' => 'withdrew/rejected',
                            'add_remark' => 'added a remark',
                            'note_added' => 'added a remark',
                        ];
                        $text = $labelMap[$key] ?? 'performed an action';
                        break;
                }

                $activities->push([
                    'type'  => 'action',
                    'stage' => $stageForAction,
                    'by'    => $userName,
                    'at'    => $log->created_at,
                    'text'  => $text,
                    'meta'  => [
                        'event'   => $log->event,
                        'payload' => $payload,
                    ],
                ]);
            }
        }

       

        // Sort and trim fields for frontend
        $stageActivities = $activities
            ->sortByDesc(fn($a) => $a['at'] ?? now())
            ->values()
            ->map(function($a){
                return [
                    'type' => $a['type'],
                    'stage' => $a['stage'],
                    'by' => $a['by'],
                    'at' => $a['at'],
                    'text' => $a['text'],
                    'meta' => $a['meta'],
                ];
            });

        return Inertia::render('Company/Applicants/ListOfApplicants/ApplicantProfile', [
            'applicant' => $application,
            'graduate' => $graduate,
            'stageActivities' => $stageActivities,
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
            'education' => $education,
            'highestEducation' => $highestEducation,
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
            'referralCertificates' => $referralCertificates,
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

            $fromId    = $application->job_pipeline_stage_id;
            $fromStage = $fromId ? JobPipelineStage::find($fromId) : null;
            $stage     = JobPipelineStage::find($data['job_pipeline_stage_id']);

            if ($stage) {
                $application->job_pipeline_stage_id = $stage->id;
                // keep legacy string column aligned
                $application->stage = $stage->slug;
                $changed[] = 'stage';

                // REPLACED: flexible payload based on available columns
                $payload = [
                    'job_application_id' => $application->id,
                    'changed_by'         => Auth::id(),
                ];

                // from_*
                if (Schema::hasColumn('job_application_stage_logs','from_stage_id')) {
                    $payload['from_stage_id'] = $fromId;
                } elseif (Schema::hasColumn('job_application_stage_logs','from_stage_slug')) {
                    $payload['from_stage_slug'] = $fromStage?->slug;
                } elseif (Schema::hasColumn('job_application_stage_logs','from_stage')) {
                    $payload['from_stage'] = $fromStage?->name ?? $fromStage?->slug;
                }

                // to_*
                if (Schema::hasColumn('job_application_stage_logs','to_stage_id')) {
                    $payload['to_stage_id'] = $stage->id;
                } elseif (Schema::hasColumn('job_application_stage_logs','to_stage_slug')) {
                    $payload['to_stage_slug'] = $stage->slug;
                } elseif (Schema::hasColumn('job_application_stage_logs','to_stage')) {
                    $payload['to_stage'] = $stage->name ?? $stage->slug;
                }

                if (Schema::hasColumn('job_application_stage_logs','changed_at')) {
                    $payload['changed_at'] = now();
                }

                JobApplicationStageLog::create($payload);
            }
        }

        if (in_array('stage', $changed) && method_exists($application,'syncStatusFromStage')) {
            if ($application->syncStatusFromStage() && !in_array('status',$changed)) {
                $changed[] = 'status';
            }
        }

        if ($changed) $application->save();

         // NEW: log remark to stage activities when notes change
        if (in_array('notes', $changed) && Schema::hasTable('job_application_action_logs')) {
            try {
                DB::table('job_application_action_logs')->insert([
                    'job_application_id' => $application->id,
                    'user_id' => Auth::id(),
                    'action_key' => 'add_remark',
                    'event' => 'remark',
                    'payload' => $application->notes, // store latest remark text
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // ignore logging failure
            }
        }

        return back()->with('success', 'Updated: '.implode(', ',$changed));
    }

    // public function scheduleInterview(Request $request, JobApplication $application)
    // {
    //     $request->validate([
    //         'scheduled_at' => 'required|date|after:now',
    //         'location' => 'nullable|string|max:255',
    //     ]);

    //     $interview = Interview::create([
    //         'job_application_id' => $application->id,
    //         'scheduled_at' => $request->scheduled_at,
    //         'location' => $request->location,
    //     ]);

    //     $interview->load('jobApplication.job.company');
        
    //     // Send notification to graduate/user
    //     if ($application->graduate && $application->graduate->user) {
    //     $application->graduate->user->notify(new InterviewScheduledNotification($interview));
    //     }


    //     // (Optional) Integrate with Google/Outlook Calendar here and save event ID

    //     return redirect()->back()->with('success', 'Interview scheduled and notification sent.');
    // }

    // public function storeOffer(Request $request, JobApplication $application)
    // {
    //     $request->validate([
    //         'offered_salary' => 'required|numeric|min:0',
    //         'start_date' => 'required|date|after:today',
    //         'offer_letter' => 'nullable|file|mimes:pdf|max:2048',
    //     ]);

    //     $offerData = [
    //         'job_application_id' => $application->id,
    //         'offered_salary' => $request->offered_salary,
    //         'start_date' => $request->start_date,
    //     ];

    //     if ($request->hasFile('offer_letter')) {
    //         $offerData['offer_letter_path'] = $request->file('offer_letter')->store('offer_letters', 'public');
    //     }

    //     $offer = JobOffer::create($offerData);

    //     // Update application status
    //     $application->status = 'offer_sent';
    //     $application->save();

    //     // Notify applicant
    //     if ($application->graduate && $application->graduate->user) {
    //         $application->graduate->user->notify(new ApplicationStatusUpdated($application, $application->status));
    //     }

    //     return redirect()->back()->with('success', 'Job offer sent successfully.');
    // }

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
