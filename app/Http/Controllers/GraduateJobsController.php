<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobOffer;
use App\Models\Referral;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\ApplicantScreeningService;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use App\Notifications\OfferAcceptedNotification;
use App\Notifications\OfferDeclinedNotification;

class GraduateJobsController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $appliedJobIds = [];
        if ($user && $user->graduate) {
            $appliedJobIds = JobApplication::where('graduate_id', $user->graduate->id)
                ->pluck('job_id')
                ->toArray();
        }

        $hideApplied = $request->boolean('hideApplied', false);

        // Fetch filter options
        $industries = \App\Models\Sector::select('id', 'name')->get();
        $companies = \App\Models\Company::select('id', 'company_name')->get();
        $jobTypes = \App\Models\JobType::pluck('type', 'id');
        $locations = \App\Models\Location::select('id', 'barangay')
            ->whereNotNull('barangay')
            ->where('barangay', '!=', '')
            ->get()
            ->unique('barangay')
            ->mapWithKeys(function ($loc) {
                return [$loc->id => $loc->barangay];
        });
        $experienceLevels = Job::distinct()->pluck('job_experience_level');

        // Fetch jobs with relationships for poster info
        $jobs = Job::with(['company', 'institution', 'peso', 'sector', 'category', 'jobTypes', 'salary'])
            ->where('status', 'open')
            ->where('is_approved', 1)
            ->when($hideApplied && !empty($appliedJobIds), function ($q) use ($appliedJobIds) {
                $q->whereNotIn('id', $appliedJobIds);
            })
            ->when($request->keywords, function ($query, $keywords) {
                $query->where(function ($q) use ($keywords) {
                    $q->where('job_title', 'like', "%{$keywords}%")
                        ->orWhere('job_description', 'like', "%{$keywords}%");
                });
            })
            ->when($request->jobType, function ($q, $type) {
                $q->whereHas('jobTypes', fn($sub) => $sub->where('job_types.id', $type));
            })
           ->when($request->location, function ($q, $location) {
                $barangay = null;
                if (is_numeric($location)) {
                    $loc = \App\Models\Location::find($location);
                    $barangay = $loc?->barangay ?? null;
                }
                if (!$barangay) {
                    $barangay = (string)$location;
                }
                // match jobs where location string contains the barangay
                $q->where('location', 'like', "%{$barangay}%");
            })
            ->when($request->industry, fn($q, $industry) => $q->where('sector_id', $industry))
            ->when($request->salaryMin, function ($q, $min) {
                $q->whereHas('salary', fn($sub) => $sub->where('job_min_salary', '>=', $min));
            })
            ->when($request->salaryMax, function ($q, $max) {
                $q->whereHas('salary', fn($sub) => $sub->where('job_max_salary', '<=', $max));
            })
            ->when($request->skills, function ($q, $skills) {
                foreach ((array)$skills as $skill) {
                    $q->whereJsonContains('skills', $skill);
                }
            })
            ->when($request->experience, fn($q, $exp) => $q->where('job_experience_level', $exp))
            ->when($request->company, fn($q, $company) => $q->where('company_id', $company))
            ->latest()
            ->paginate(10)
            ->withQueryString();


        $graduateId = $user && $user->graduate ? $user->graduate->id : null;

        $myApplications = Job::with(['company', 'institution', 'peso', 'sector', 'category', 'jobTypes', 'salary'])
            ->whereIn('id', $appliedJobIds)
            ->get()
            ->map(function ($j) use ($user, $graduateId) {
                // find the application record for this graduate & job
                $application = JobApplication::where('graduate_id', $graduateId)
                    ->where('job_id', $j->id)
                    ->first();

                return [
                    'id' => $j->id,
                    'job_title' => $j->job_title,
                    'company' => $j->company ? [
                        'id' => $j->company->id,
                        'company_name' => $j->company->company_name,
                    ] : null,
                    'locations' => $j->location ? [['address' => $j->location]] : [],
                    'jobTypes' => $j->jobTypes ? $j->jobTypes->map(fn($t) => ['type' => $t->type])->values()->all() : [],
                    'job_experience_level' => $j->job_experience_level,
                    'salary' => $j->salary ? [
                        'job_min_salary' => $j->salary->job_min_salary ?? null,
                        'job_max_salary' => $j->salary->job_max_salary ?? null,
                        'salary_type' => $j->salary->salary_type ?? null,
                    ] : null,
                    // application-specific fields
                    'application' => $application ? [
                        'id' => $application->id,
                        'status' => $application->status,
                        'stage' => $application->stage,
                        'applied_at' => $application->applied_at?->toDateTimeString() ?? null,
                    ] : null,
                ];
            })->values()->toArray();

        // Clean skills field before sending to Inertia
        // $jobs->getCollection()->transform(function ($job) {
        //     // Decode JSON skills if stored as string, or use as is if array
        //     $skillsArray = [];
        //     if (is_string($job->skills)) {
        //         $decoded = json_decode($job->skills, true);
        //         $skillsArray = is_array($decoded) ? $decoded : [];
        //     } elseif (is_array($job->skills)) {
        //         $skillsArray = $job->skills;
        //     }


        //     // Clean each skill
        //     $cleanSkills = array_map(function ($skill) {
        //         $skill = trim($skill);
        //         // Remove spaces inside the skill (turn "P y t h o n" into "Python")
        //         $skill = preg_replace('/\s+/', '', $skill);
        //         // Remove any brackets or quotes
        //         $skill = str_replace(['[', ']', '"', "'"], '', $skill);
        //         return $skill;
        //     }, $skillsArray);

        //     // Assign back clean skills
        //     $job->skills = $cleanSkills;

        //     return $job;
        // });
        $jobs->getCollection()->transform(function ($job) use ($user, $jobTypes) {
            // Decode JSON skills if stored as string, or use as is if array
            $skillsArray = [];
            if (is_string($job->skills)) {
                $decoded = json_decode($job->skills, true);
                $skillsArray = is_array($decoded) ? $decoded : [];
            } elseif (is_array($job->skills)) {
                $skillsArray = $job->skills;
            }

            // Clean each skill
            $cleanSkills = array_map(function ($skill) {
                $skill = trim($skill);
                // Remove spaces inside the skill (turn "P y t h o n" into "Python")
                $skill = preg_replace('/\s+/', '', $skill);
                // Remove any brackets or quotes
                $skill = str_replace(['[', ']', '"', "'"], '', $skill);
                return $skill;
            }, $skillsArray);
            $job->skills = $cleanSkills;

            // job_type_names mapping (same logic as search())
            if ($job->relationLoaded('jobTypes') && $job->jobTypes && $job->jobTypes->count()) {
                $job->job_type_names = $job->jobTypes->pluck('type')->filter()->values()->all();
            } elseif (is_array($job->job_type)) {
                $job->job_type_names = collect($job->job_type)->map(function ($id) use ($jobTypes) {
                    return $jobTypes[$id] ?? null;
                })->filter()->values()->all();
            } elseif (is_numeric($job->job_type)) {
                $job->job_type_names = [$jobTypes[$job->job_type] ?? null];
            } elseif (is_string($job->job_type) && $job->job_type !== '') {
                $job->job_type_names = [$job->job_type];
            } else {
                $job->job_type_names = [];
            }

            // Provide locations array (compat with legacy Vue)
            $job->locations = $job->location
                ? [['address' => $job->location]]
                : [];

            // Match percentage
            $match = 0;
            if ($user && $user->graduate) {
                $screening = (new ApplicantScreeningService())->screen($user->graduate, $job);
                $match = $screening['match_percentage'] ?? 0;
            }
            $job->match_percentage = $match;

            return $job;
        });

        $jobOffers = [];
        if ($user && $user->graduate) {
            $graduateId = $user->graduate->id;
            $jobOffers = JobOffer::with(['application.job.company', 'message'])
                ->whereHas('application', fn($q) => $q->where('graduate_id', $graduateId))
                ->latest()
                ->get()
                ->map(function ($o) {
                    return [
                        'id' => $o->id,
                        'job_title' => $o->job_title ?? optional($o->application->job)->job_title,
                        'company' => $o->application && $o->application->job && $o->application->job->company
                            ? [
                                'id' => $o->application->job->company->id,
                                'company_name' => $o->application->job->company->company_name,
                            ] : null,
                        'body' => $o->body,
                        'file_url' => $o->file_path ? Storage::url($o->file_path) : null,
                        'start_date' => $o->start_date?->toDateString(),
                        'status' => $o->status,
                        'responded_at' => $o->responded_at?->toISOString(),
                        // include original application id so frontend can call accept/decline routes
                        'application_id' => $o->job_application_id,
                    ];
                })->values()->toArray();
        }

        return Inertia::render('Frontend/JobSearch', [
            'jobs' => $jobs,
            'appliedJobIds' => $appliedJobIds,
            'myApplications' => $myApplications,
            'hideApplied' => $hideApplied,
            'industries' => $industries,
            'companies' => $companies,
            'jobTypes' => $jobTypes,
            'locations' => $locations,
            'experienceLevels' => $experienceLevels,
            'jobOffers' => $jobOffers,
            'query' => $request->query(),
        ]);
    }
    public function search(Request $request)
    {

        $request->validate([
            'keywords' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $appliedJobIds = [];
        if ($user && $user->graduate) {
            $appliedJobIds = JobApplication::where('graduate_id', $user->graduate->id)
                ->pluck('job_id')
                ->toArray();
        }

        $hideApplied = $request->boolean('hideApplied', false);


        if ($user && $user->role === 'graduate' && $user->graduate && $request->keywords) {
            Log::info('Search method called', [
                'user_id' => $user ? $user->id : null,
                'graduate_id' => $user && $user->graduate ? $user->graduate->id : null,
                'keywords' => $request->keywords,
            ]);
            \App\Models\JobSearchHistory::create([
                'graduate_id' => $user->graduate->id,
                'keywords' => $request->keywords,
            ]);
        }
        // Fetch filter options (same as index)
        $industries = \App\Models\Sector::select('id', 'name')->get();
        $companies = \App\Models\Company::select('id', 'company_name')->get();
        $jobTypes = \App\Models\JobType::pluck('type', 'id');
        $locations = \App\Models\Location::select('id', 'barangay')
            ->whereNotNull('barangay')
            ->where('barangay', '!=', '')
            ->get()
            ->unique('barangay')
            ->mapWithKeys(function ($loc) {
                return [$loc->id => $loc->barangay];
        });
        $experienceLevels = Job::distinct()->pluck('job_experience_level');

        // Fetch jobs with relationships for poster info (same as index)
        $jobs = Job::with(['company', 'institution', 'peso', 'sector', 'category', 'jobTypes', 'salary'])
            ->where('status', 'open')
            ->where('is_approved', 1)
            ->when($hideApplied && !empty($appliedJobIds), function ($q) use ($appliedJobIds) {
                $q->whereNotIn('id', $appliedJobIds);
            })
            ->when($request->keywords, function ($query, $keywords) {
                $query->where(function ($q) use ($keywords) {
                    $q->where('job_title', 'like', "%{$keywords}%")
                        ->orWhere('job_description', 'like', "%{$keywords}%");
                });
            })
            ->when($request->jobType, function ($q, $type) {
                // filter by job_types.id (NOT pivot column name directly)
                $q->whereHas('jobTypes', fn($sub) => $sub->where('job_types.id', $type));
            })
            ->when($request->location, function ($q, $location) {
                $barangay = null;
                if (is_numeric($location)) {
                    $loc = \App\Models\Location::find($location);
                    $barangay = $loc?->barangay ?? null;
                }
                if (!$barangay) {
                    $barangay = (string)$location;
                }
                // match jobs where location string contains the barangay
                $q->where('location', 'like', "%{$barangay}%");
            })
            ->when($request->industry, fn($q, $industry) => $q->where('sector_id', $industry))
            ->when($request->salaryMin, function ($q, $min) {
                $q->whereHas('salary', fn($sub) => $sub->where('job_min_salary', '>=', $min));
            })
            ->when($request->salaryMax, function ($q, $max) {
                $q->whereHas('salary', fn($sub) => $sub->where('job_max_salary', '<=', $max));
            })
            ->when($request->skills, function ($q, $skills) {
                foreach ((array)$skills as $skill) {
                    $q->whereJsonContains('skills', $skill);
                }
            })
            ->when($request->experience, fn($q, $exp) => $q->where('job_experience_level', $exp))
            ->when($request->company, fn($q, $company) => $q->where('company_id', $company))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $graduateId = $user && $user->graduate ? $user->graduate->id : null;

        $myApplications = Job::with(['company', 'institution', 'peso', 'sector', 'category', 'jobTypes', 'salary'])
            ->whereIn('id', $appliedJobIds)
            ->get()
            ->map(function ($j) use ($user, $graduateId) {
                // find the application record for this graduate & job
                $application = JobApplication::where('graduate_id', $graduateId)
                    ->where('job_id', $j->id)
                    ->first();

                return [
                    'id' => $j->id,
                    'job_title' => $j->job_title,
                    'company' => $j->company ? [
                        'id' => $j->company->id,
                        'company_name' => $j->company->company_name,
                    ] : null,
                    'locations' => $j->location ? [['address' => $j->location]] : [],
                    'jobTypes' => $j->jobTypes ? $j->jobTypes->map(fn($t) => ['type' => $t->type])->values()->all() : [],
                    'job_experience_level' => $j->job_experience_level,
                    'salary' => $j->salary ? [
                        'job_min_salary' => $j->salary->job_min_salary ?? null,
                        'job_max_salary' => $j->salary->job_max_salary ?? null,
                        'salary_type' => $j->salary->salary_type ?? null,
                    ] : null,
                    // application-specific fields
                    'application' => $application ? [
                        'id' => $application->id,
                        'status' => $application->status,
                        'stage' => $application->stage,
                        'applied_at' => $application->applied_at?->toDateTimeString() ?? null,
                    ] : null,
                ];
            })->values()->toArray();

        // Clean skills field before sending to Inertia
        // $jobs->getCollection()->transform(function ($job) {
        //     // Decode JSON skills if stored as string, or use as is if array
        //     $skillsArray = [];
        //     if (is_string($job->skills)) {
        //         $decoded = json_decode($job->skills, true);
        //         $skillsArray = is_array($decoded) ? $decoded : [];
        //     } elseif (is_array($job->skills)) {
        //         $skillsArray = $job->skills;
        //     }


        //     // Clean each skill
        //     $cleanSkills = array_map(function ($skill) {
        //         $skill = trim($skill);
        //         // Remove spaces inside the skill (turn "P y t h o n" into "Python")
        //         $skill = preg_replace('/\s+/', '', $skill);
        //         // Remove any brackets or quotes
        //         $skill = str_replace(['[', ']', '"', "'"], '', $skill);
        //         return $skill;
        //     }, $skillsArray);

        //     // Assign back clean skills
        //     $job->skills = $cleanSkills;

        //     return $job;
        // });

        $jobs->getCollection()->transform(function ($job) use ($user, $jobTypes) {
            // Decode JSON skills if stored as string, or use as is if array
            $skillsArray = [];
            if (is_string($job->skills)) {
                $decoded = json_decode($job->skills, true);
                $skillsArray = is_array($decoded) ? $decoded : [];
            } elseif (is_array($job->skills)) {
                $skillsArray = $job->skills;
            }

            // Clean each skill
            $cleanSkills = array_map(function ($skill) {
                $skill = trim($skill);
                // Remove spaces inside the skill (turn "P y t h o n" into "Python")
                $skill = preg_replace('/\s+/', '', $skill);
                // Remove any brackets or quotes
                $skill = str_replace(['[', ']', '"', "'"], '', $skill);
                return $skill;
            }, $skillsArray);
            $job->skills = $cleanSkills;

            // job_type_names mapping (same logic as search())
            if ($job->relationLoaded('jobTypes') && $job->jobTypes && $job->jobTypes->count()) {
                $job->job_type_names = $job->jobTypes->pluck('type')->filter()->values()->all();
            } elseif (is_array($job->job_type)) {
                $job->job_type_names = collect($job->job_type)->map(function ($id) use ($jobTypes) {
                    return $jobTypes[$id] ?? null;
                })->filter()->values()->all();
            } elseif (is_numeric($job->job_type)) {
                $job->job_type_names = [$jobTypes[$job->job_type] ?? null];
            } elseif (is_string($job->job_type) && $job->job_type !== '') {
                $job->job_type_names = [$job->job_type];
            } else {
                $job->job_type_names = [];
            }

            // Provide locations array (compat with legacy Vue)
            $job->locations = $job->location
                ? [['address' => $job->location]]
                : [];

            // Match percentage
            $match = 0;
            if ($user && $user->graduate) {
                $screening = (new ApplicantScreeningService())->screen($user->graduate, $job);
                $match = $screening['match_percentage'] ?? 0;
            }
            $job->match_percentage = $match;

            return $job;
        });

        $jobOffers = [];
        if ($user && $user->graduate) {
            $graduateId = $user->graduate->id;
            $jobOffers = JobOffer::with(['application.job.company', 'message'])
                ->whereHas('application', fn($q) => $q->where('graduate_id', $graduateId))
                ->latest()
                ->get()
                ->map(function ($o) {
                    return [
                        'id' => $o->id,
                        'job_title' => $o->job_title ?? optional($o->application->job)->job_title,
                        'company' => $o->application && $o->application->job && $o->application->job->company
                            ? [
                                'id' => $o->application->job->company->id,
                                'company_name' => $o->application->job->company->company_name,
                            ] : null,
                        'body' => $o->body,
                        'file_url' => $o->file_path ? Storage::url($o->file_path) : null,
                        'start_date' => $o->start_date?->toDateString(),
                        'status' => $o->status,
                        'responded_at' => $o->responded_at?->toISOString(),
                        // include original application id so frontend can call accept/decline routes
                        'application_id' => $o->job_application_id,
                    ];
                })->values()->toArray();
        }

        return Inertia::render('Frontend/JobSearch', [
            'jobs' => $jobs,
            'appliedJobIds' => $appliedJobIds,
            'myApplications' => $myApplications,
            'hideApplied' => $hideApplied,
            'industries' => $industries,
            'companies' => $companies,
            'jobTypes' => $jobTypes,
            'locations' => $locations,
            'experienceLevels' => $experienceLevels,
            'jobOffers' => $jobOffers,
            'query' => $request->query(),


        ]);
    }


    public function recommendations(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->graduate) {
            return response()->json(['recommendations' => []]);
        }

        $graduate = $user->graduate;

        // Gather graduate profile context (same as before)
        $graduateSkills = \DB::table('graduate_skills')
            ->join('skills', 'graduate_skills.skill_id', '=', 'skills.id')
            ->where('graduate_skills.graduate_id', $graduate->id)
            ->select('skills.name', 'graduate_skills.type')
            ->get();

        $graduateTechnicalSkills = $graduateSkills->where('type', 'Technical Skills')->pluck('name')->all();
        $graduateOtherSkills     = $graduateSkills->where('type', '!=', 'Technical Skills')->pluck('name')->all();

        $normalizedGraduateTechnicalSkills = collect($graduateTechnicalSkills)->map(function ($s) {
            $s = trim($s);
            $s = preg_replace('/\s+/', '', $s);
            $s = str_replace(['[',']','"',"'"], '', $s);
            return strtolower($s);
        })->unique()->toArray();

        $normalizedGraduateOtherSkills = collect($graduateOtherSkills)->map(function ($s) {
            $s = trim($s);
            $s = preg_replace('/\s+/', '', $s);
            $s = str_replace(['[',']','"',"'"], '', $s);
            return strtolower($s);
        })->unique()->toArray();

        $education = \App\Models\GraduateEducation::where('graduate_id', $graduate->id)
            ->orderByDesc('is_current')
            ->orderByDesc('end_date')
            ->first();
        $graduateProgram = $education ? trim(strtolower($education->program)) : null;

        $experiences     = \App\Models\Experience::where('graduate_id', $graduate->id)->get();
        $experienceTitles = $experiences->pluck('title')->filter()->unique()->toArray();

        $preferences = \App\Models\EmploymentPreference::where('graduate_id', $graduate->id)->first();

        $parsePrefArray = function ($value) {
            if (is_array($value)) return array_values(array_filter(array_map('trim', $value)));
            if (is_string($value)) {
                $dec = @json_decode($value, true);
                if (is_array($dec)) return array_values(array_filter(array_map('trim', $dec)));
                return array_values(array_filter(array_map('trim', explode(',', $value))));
            }
            return [];
        };

        $preferredJobTypes        = $preferences ? $parsePrefArray($preferences->job_type) : [];
        $preferredLocations       = $preferences ? $parsePrefArray($preferences->location) : [];
        $preferredWorkEnvironments= $preferences ? $parsePrefArray($preferences->work_environment) : [];
        $minSalary                = $preferences->employment_min_salary ?? null;
        $maxSalary                = $preferences->employment_max_salary ?? null;
        $salaryType               = $preferences->salary_type ?? null;

        // Incoming search filters
        $keywords    = $request->keywords;
        $jobTypeId   = $request->jobType;
        $locationIn  = $request->location;
        $industry    = $request->industry;
        $salaryMin   = $request->salaryMin;
        $salaryMax   = $request->salaryMax;
        $skillsFilt  = $request->skills;
        $experience  = $request->experience;
        $company     = $request->company;
        $hideApplied = $request->boolean('hideApplied', false);

        $appliedJobIds = [];
       if ($user && $user->graduate) {
           $appliedJobIds = JobApplication::where('graduate_id', $user->graduate->id)
               ->pluck('job_id')
               ->toArray();
       }

        // ---- PATCH: location filter uses barangay ----
        $jobsQuery = Job::with(['company','jobTypes','salary'])
            ->where('status', 'open')
            ->where('is_approved', 1)
            ->when($hideApplied && !empty($appliedJobIds), function ($q) use ($appliedJobIds) {
               $q->whereNotIn('id', $appliedJobIds);
           })
            ->when($keywords, function ($q, $kw) {
                $q->where(function ($sub) use ($kw) {
                    $sub->where('job_title', 'like', "%{$kw}%")
                        ->orWhere('job_description', 'like', "%{$kw}%");
                });
            })
            ->when($jobTypeId, function ($q, $type) {
                $q->whereHas('jobTypes', fn($sub) => $sub->where('job_types.id', $type));
            })
            ->when($locationIn, function ($q, $loc) {
                $barangay = null;
                if (is_numeric($loc)) {
                    $found = \App\Models\Location::find($loc);
                    $barangay = $found?->barangay;
                }
                if (!$barangay) $barangay = (string)$loc;
                $q->where('location', 'like', "%{$barangay}%");
            })
            ->when($industry, fn($q, $ind) => $q->where('sector_id', $ind))
            ->when($salaryMin, fn($q, $min) => $q->whereHas('salary', fn($s) => $s->where('job_min_salary', '>=', $min)))
            ->when($salaryMax, fn($q, $max) => $q->whereHas('salary', fn($s) => $s->where('job_max_salary', '<=', $max)))
            ->when($skillsFilt, function ($q, $skills) {
                foreach ((array)$skills as $skill) {
                    $q->whereJsonContains('skills', $skill);
                }
            })
            ->when($experience, fn($q, $exp) => $q->where('job_experience_level', $exp))
            ->when($company, fn($q, $comp) => $q->where('company_id', $comp))
            ->limit(150);

        $jobs = $jobsQuery->get();

        // ---- Scoring Weights ----
        $weights = [
            'technical_skills' => 7,
            'other_skills'     => 2,
            'education'        => 5,
            'experience'       => 5,
            'job_type'         => 0.143,
            'location'         => 0.143,
            'work_environment' => 0.143,
            'min_salary'       => 0.143,
            'max_salary'       => 0.143,
            'salary_type'      => 0.143,
            'keywords'         => 0.143,
        ];
        $totalWeight = array_sum($weights);

        $recommendations = [];

        foreach ($jobs as $job) {
            $labels = [];
            $score  = 0;

            // Skills normalization
            $skillsArray = [];
            if (is_string($job->skills)) {
                $dec = json_decode($job->skills, true);
                $skillsArray = is_array($dec) ? $dec : [];
            } elseif (is_array($job->skills)) {
                $skillsArray = $job->skills;
            }
            $cleanSkills = array_map(function ($s) {
                $s = trim($s);
                $s = preg_replace('/\s+/', '', $s);
                $s = str_replace(['[',']','"',"'"], '', $s);
                return strtolower($s);
            }, $skillsArray);
            $normalizedJobSkills = collect($cleanSkills)->unique()->toArray();

            // Program names tied to job
            $jobProgramNames = \DB::table('job_program')
                ->join('programs','job_program.program_id','=','programs.id')
                ->where('job_program.job_id', $job->id)
                ->pluck('programs.name')
                ->map(fn($n)=>trim(strtolower($n)))->toArray();

            // m1 Technical skills
            $technicalSkillMatch = (int)collect($normalizedGraduateTechnicalSkills)->contains(fn($gs)=>in_array($gs,$normalizedJobSkills));
            if ($technicalSkillMatch) $labels[] = 'Technical Skills';
            $score += $technicalSkillMatch * $weights['technical_skills'];

            // m1b Other skills (only if no technical match)
            $otherSkillMatch = 0;
            if (!$technicalSkillMatch) {
                $otherSkillMatch = (int)collect($normalizedGraduateOtherSkills)->contains(fn($os)=>in_array($os,$normalizedJobSkills));
                if ($otherSkillMatch) $labels[] = 'Other Skills (Soft, Language)';
                $score += $otherSkillMatch * $weights['other_skills'];
            }

            // m2 Education
            $educationMatch = ($graduateProgram && in_array($graduateProgram, $jobProgramNames)) ? 1 : 0;
            if ($educationMatch) $labels[] = 'Education';
            $score += $educationMatch * $weights['education'];

            // m3 Experience (title match)
            $experienceMatch = 0;
            foreach ($experienceTitles as $title) {
                if (stripos($job->job_title, $title) !== false) { $experienceMatch = 1; break; }
            }
            if ($experienceMatch) $labels[] = 'Experience';
            $score += $experienceMatch * $weights['experience'];

            // m4 Preferred Job Type (any overlap)
            $jobTypeList = $job->jobTypes ? $job->jobTypes->pluck('type')->map(fn($t)=>trim($t))->toArray() : [];
            $jobTypeMatch = count(array_intersect($preferredJobTypes, $jobTypeList)) > 0 ? 1 : 0;
            if ($jobTypeMatch) $labels[] = 'Preferred Job Type';
            $score += $jobTypeMatch * $weights['job_type'];

            // m5 Preferred Location
            $locationMatch = $job->location && in_array($job->location, $preferredLocations) ? 1 : 0;
            if ($locationMatch) $labels[] = 'Preferred Location';
            $score += $locationMatch * $weights['location'];

            // m6 Work Environment
            $workEnvMatch = $job->work_environment && in_array($job->work_environment, $preferredWorkEnvironments) ? 1 : 0;
            if ($workEnvMatch) $labels[] = 'Preferred Work Environment';
            $score += $workEnvMatch * $weights['work_environment'];

            // m7 Min Salary
            $minSalaryMatch = ($minSalary && $job->job_min_salary >= $minSalary) ? 1 : 0;
            if ($minSalaryMatch) $labels[] = 'Preferred Min Salary';
            $score += $minSalaryMatch * $weights['min_salary'];

            // m8 Max Salary
            $maxSalaryMatch = ($maxSalary && $job->job_max_salary && $job->job_max_salary <= $maxSalary) ? 1 : 0;
            if ($maxSalaryMatch) $labels[] = 'Preferred Max Salary';
            $score += $maxSalaryMatch * $weights['max_salary'];

            // m9 Salary Type
            $salaryTypeMatch = ($salaryType && $job->job_salary_type && stripos($job->job_salary_type, $salaryType) !== false) ? 1 : 0;
            if ($salaryTypeMatch) $labels[] = 'Preferred Salary Type';
            $score += $salaryTypeMatch * $weights['salary_type'];

            // m10 Keywords (already filtered by keywords but keep binary score)
            $keywordsMatch = $keywords ? 1 : 0;
            if ($keywordsMatch) $labels[] = 'Keywords';
            $score += $keywordsMatch * $weights['keywords'];

            if (!empty($labels)) {
                $job->match_labels = array_values(array_unique($labels));
                $job->match_score = $score;
                $job->match_percentage = $totalWeight > 0 ? round(($score / $totalWeight) * 100) : 0;
                // Provide compatibility locations array
                $job->locations = $job->location ? [['address' => $job->location]] : [];
                $recommendations[] = $job;
            }
        }

        usort($recommendations, fn($a,$b) => $b->match_score <=> $a->match_score);

        // limit final output
        $recommendations = array_slice($recommendations, 0, 10);

        return response()->json(['recommendations' => $recommendations]);
    }
    public function applyForJob(Request $request)
    {
        $user = Auth::user();
        $graduate = $user->graduate;

        $validated = $request->validate([
            'job_id' => 'required|exists:jobs,id',
            'applied_at' => 'nullable|date',
            'interview_date' => 'nullable|date',
            'resume_id' => 'nullable|exists:resumes,id',
            'cover_letter' => 'nullable|string',
            'cover_letter_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'additional_documents' => 'nullable|array',
        ]);

        // Prepare additional documents payload
        $additionalDocs = $validated['additional_documents'] ?? [];

        // Store optional cover letter file
        if ($request->hasFile('cover_letter_file')) {
            $file = $request->file('cover_letter_file');
            $path = $file->store('cover-letters', 'public');
            $additionalDocs['cover_letter'] = [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'url'  => Storage::url($path),
                'size' => $file->getSize(),
                'uploaded_at' => now()->toISOString(),
            ];
        }

        // Create application
        $application = JobApplication::create([
            'user_id' => $user->id,
            'graduate_id' => $graduate->id,
            'job_id' => $validated['job_id'],
            'resume_id' => $validated['resume_id'] ?? null,
            'cover_letter' => $validated['cover_letter'] ?? null,
            'additional_documents' => $additionalDocs ?: null, // save JSON with cover letter file if present
            'status' => 'applied',
            'stage' => 'applying',
            'applied_at' => now(),
        ]);

        $job = $application->job;
        $screening = (new ApplicantScreeningService())->screen($graduate, $job);

        $application->is_shortlisted = $screening['is_shortlisted'] ?? false;
        $application->screening_label = $screening['screening_label'] ?? null;
        $application->screening_feedback = $screening['screening_feedback'] ?? null;
        $application->match_percentage = $screening['match_percentage'] ?? 0;

        // Set status/stage as required:
        if (($screening['match_percentage'] ?? 0) >= 60) {
            // High match → move to Screening
            $application->status = 'screening';
            $application->stage = 'screening';
        } else {
            // Otherwise stay in Applied/Applying
            $application->status = 'applied';
            $application->stage = 'applied';
        }

        $application->save();

        return back()->with('success', 'Application submitted successfully.');
    }

    public function requestReferral(Request $request)
    {
        $user = auth()->user();
        $graduate = $user->graduate;

        \App\Models\Referral::create([
            'graduate_id' => $graduate->id,
            'job_id' => $request->job_id,
            'company_id' => $request->company_id, // <-- add this line
            'status' => 'pending',
        ]);

        return back()->with('success', 'Referral request sent!');
    }


    public function show(Job $job)
    {
        $job->load(['company', 'institution', 'peso', 'sector', 'category', 'jobTypes', 'salary']);

        // Normalize skills
        if (is_string($job->skills)) {
            $decoded = json_decode($job->skills, true);
            $job->skills = is_array($decoded) ? array_map(fn($s) => trim(str_replace(['[', ']', '"', "'"], '', $s)), $decoded) : [];
        } elseif (!is_array($job->skills)) {
            $job->skills = [];
        }

        // Map Job Types to names
        $jobTypeNames = [];
        if ($job->relationLoaded('jobTypes') && $job->jobTypes && $job->jobTypes->count()) {
            // expects JobType has a 'type' column
            $jobTypeNames = $job->jobTypes->pluck('type')->filter()->values()->all();
        } elseif (is_array($job->job_type)) {
            $jobTypeNames = array_values(array_filter($job->job_type, fn($v) => !is_null($v) && $v !== ''));
        } elseif (is_string($job->job_type) && $job->job_type !== '') {
            $jobTypeNames = [$job->job_type];
        }

        // Map Work Environment IDs to labels
        $workEnvMap = [
            1 => 'On-site',
            2 => 'Remote',
            3 => 'Hybrid',
        ];
        $we = $job->work_environment;

        // Convert to array of values
        if (is_string($we)) {
            $json = json_decode($we, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $we = $json;
            } else {
                $we = array_map('trim', array_filter(explode(',', $we)));
            }
        } elseif ($we === null) {
            $we = [];
        } elseif (!is_array($we)) {
            $we = [$we];
        }

        // Map to labels if numeric ids, keep text if already labels
        $workEnvLabels = collect($we)->map(function ($v) use ($workEnvMap) {
            if (is_numeric($v)) {
                $v = (int)$v;
                return $workEnvMap[$v] ?? (string)$v;
            }
            return (string)$v;
        })->filter()->values()->all();

        // Attach normalized fields to job payload
        $job->setAttribute('job_type_names', $jobTypeNames);
        $job->setAttribute('work_environment_labels', $workEnvLabels);

        $application = null;
        if (Auth::check() && Auth::user()->graduate) {
            $application = JobApplication::where('graduate_id', Auth::user()->graduate->id)
                ->where('job_id', $job->id)
                ->first();
        }


        return Inertia::render('Frontend/GraduateJobDetails', [
            'job' => $job,
            'application' => $application ? [
                'status' => $application->status,
                'stage' => $application->stage,
                'applied_at' => $application->applied_at,
                'interview_date' => $application->interview_date,
                'match_percentage' => $application->match_percentage,
                'screening_label' => $application->screening_label,
                'screening_feedback' => $application->screening_feedback,
            ] : null,
        ]);
    }

    public function offers(Request $request)
    {
        $user = Auth::user();
        $jobOffers = [];
        if ($user && $user->graduate) {
            $graduateId = $user->graduate->id;
            $jobOffers = JobOffer::with(['application.job.company'])
                ->whereHas('application', fn($q) => $q->where('graduate_id', $graduateId))
                ->latest()
                ->get()
                ->map(function ($o) {
                    $company = optional($o->application->job->company);
                    return [
                        'id' => $o->id,
                        'application_id' => $o->job_application_id,
                        'job_title' => $o->job_title ?: optional($o->application->job)->job_title,
                        'company' => $company ? [
                            'id' => $company->id,
                            'company_name' => $company->company_name,
                        ] : null,
                        'body' => $o->body,
                        'file_path' => $o->file_path ? Storage::url($o->file_path) : null,
                        'status' => $o->status,
                        'created_at' => $o->created_at?->toDateTimeString(),
                    ];
                })->values()->toArray();
        }

        return Inertia::render('Frontend/JobSearch', [
            'jobOffers' => $jobOffers,
        ]);
    }

    public function showReferrals(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->graduate) {
            abort(403);
        }

        $query = Referral::with(['job.company'])
            ->where('graduate_id', $user->graduate->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('company')) {
            $query->whereHas('job.company', function ($q) use ($request) {
                $q->where('company_name', 'like', '%' . $request->company . '%');
            });
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('job', fn($sub) => $sub->where('job_title', 'like', "%$search%"))
                    ->orWhereHas('job.company', fn($sub) => $sub->where('company_name', 'like', "%$search%"));
            });
        }

        $referrals = $query->latest()->paginate(10)->withQueryString();

        $referralData = $referrals->getCollection()->map(function ($ref) {
            return [
                'id' => $ref->id,
                'job_title' => $ref->job ? $ref->job->job_title : 'N/A',
                'company' => $ref->job && $ref->job->company ? $ref->job->company->company_name : 'N/A',
                'status' => $ref->status,
                'referred_at' => $ref->created_at ? $ref->created_at->toDateString() : '',
                'certificate_path' => $ref->certificate_path,
            ];
        });

        $referrals->setCollection($referralData);

        return Inertia::render('Frontend/JobReferrals', [
            'referrals' => $referrals,
            'filters' => [
                'status' => $request->status,
                'company' => $request->company,
                'search' => $request->search,
            ],
        ]);
    }
    public function showOffer(Request $request, $id)
    {
        $user = Auth::user();
        $offer = JobOffer::with(['application.job.company'])->findOrFail($id);

        // basic ownership check - ensure it belongs to this graduate
        if (!$user || !$user->graduate || ($offer->application->graduate_id ?? null) !== $user->graduate->id) {
            abort(403);
        }

        $company = optional($offer->application->job->company);
        $hrName = $offer->hr_name ?? $company->company_name ?? 'HR'; // fallback

        return Inertia::render('Frontend/JobOffers/Show', [
            'offer' => [
                'id' => $offer->id,
                'application_id' => $offer->job_application_id,
                'job_title' => $offer->job_title ?: optional($offer->application->job)->job_title,
                'company' => $company ? ['id' => $company->id, 'company_name' => $company->company_name] : null,
                'hr_name' => $hrName,
                'start_date' => $offer->start_date ? $offer->start_date->format('F j, Y') : null,
                'body' => $offer->body,
                'file_path' => $offer->file_path ? Storage::url($offer->file_path) : null,
                'status' => $offer->status,
                'created_at' => $offer->created_at?->toDateTimeString(),
            ],
        ]);
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



    public function acceptOffer(Request $request, $id)
    {
        $user = Auth::user();
        $offer = JobOffer::findOrFail($id);
        $application = JobApplication::find($offer->job_application_id);

        if (!$user || !$user->graduate || ($offer->application->graduate_id ?? null) !== $user->graduate->id) {
            abort(403);
        }

        $message = $request->input('message', 'I accept this offer. Thank you.');
        $offer->status = JobOffer::STATUS_ACCEPTED;
        $application->status = 'offer_accepted';
        $application->save();
        $offer->responded_at = now();
        $offer->save();

        // Insert action log (if table exists) so CompanyApplicationController will surface it
        try {
            if (Schema::hasTable('job_application_action_logs')) {
                DB::table('job_application_action_logs')->insert([
                    'job_application_id' => $offer->job_application_id,
                    'user_id' => $user->id,
                    'action_key' => 'offer_accepted',
                    'event' => 'offer_accepted',
                    'payload' => json_encode([
                        'offer_id' => $offer->id,
                        'message' => $message,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to insert job_application_action_logs for offer accept: ' . $e->getMessage());
        }

        // Notify employer HR users associated with the job's company
        try {
            $application = $offer->application()->with('job')->first();
            $companyId = $application->job->company_id ?? null;

            if ($companyId) {
                $hrUserIds = DB::table('human_resources')
                    ->where('company_id', $companyId)
                    ->pluck('user_id')
                    ->filter()
                    ->unique()
                    ->toArray();

                Log::info('Offer notify hrUserIds', ['company_id' => $companyId, 'hrUserIds' => $hrUserIds]);

                if (!empty($hrUserIds)) {
                    $users = User::whereIn('id', $hrUserIds)->get();
                    Log::info('Offer notify users found', ['count' => $users->count()]);

                    $url = url('/company/applicants/' . $application->id); // employer target

                    foreach ($users as $u) {
                        try {
                            // use instance notify to ensure delivery to database channel
                            $u->notify(new OfferAcceptedNotification($application, $offer, $message, $url));
                            Log::info('Notification sent to user', ['user_id' => $u->id, 'offer_id' => $offer->id]);
                        } catch (\Throwable $e) {
                            Log::error('Failed to notify user ' . $u->id, ['error' => $e->getMessage()]);
                        }
                    }
                } else {
                    Log::warning('No HR user ids found for company', ['company_id' => $companyId]);
                }
            } else {
                Log::warning('No company associated with application when notifying HR', ['application_id' => $application->id ?? null]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to notify HR on offer accept/decline: ' . $e->getMessage());
        }

        return back()->with('success', 'Offer accepted.');
    }

    public function declineOffer(Request $request, $id)
    {
        $user = Auth::user();
        $offer = JobOffer::findOrFail($id);
        $application = JobApplication::find($offer->job_application_id);

        // basic ownership check - ensure it belongs to this graduate
        if (!$user || !$user->graduate || ($offer->application->graduate_id ?? null) !== $user->graduate->id) {
            abort(403);
        }

        $message = $request->input('message', 'I decline this offer. Thank you for the opportunity.');
        $application->status = 'offer_declined';
        $application->save();
        $offer->status = JobOffer::STATUS_DECLINED ?? 'declined';
        $offer->responded_at = now();
        $offer->save();

        // Insert action log (if table exists)
        try {
            if (Schema::hasTable('job_application_action_logs')) {
                DB::table('job_application_action_logs')->insert([
                    'job_application_id' => $offer->job_application_id,
                    'user_id' => $user->id,
                    'action_key' => 'offer_declined',
                    'event' => 'offer_declined',
                    'payload' => json_encode([
                        'offer_id' => $offer->id,
                        'message' => $message,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to insert job_application_action_logs for offer decline: ' . $e->getMessage());
        }

        // Notify employer HR users associated with the job's company
        try {
            $application = $offer->application()->with('job')->first();
            $companyId = $application->job->company_id ?? null;

            if ($companyId) {
                $hrUserIds = DB::table('human_resources')
                    ->where('company_id', $companyId)
                    ->pluck('user_id')
                    ->filter()
                    ->unique()
                    ->toArray();

                Log::info('Offer notify hrUserIds', ['company_id' => $companyId, 'hrUserIds' => $hrUserIds]);

                if (!empty($hrUserIds)) {
                    $users = User::whereIn('id', $hrUserIds)->get();
                    Log::info('Offer notify users found', ['count' => $users->count()]);

                    $url = url('/company/applicants/' . $application->id); // employer target

                    foreach ($users as $u) {
                        try {
                            // use instance notify to ensure delivery to database channel
                            $u->notify(new OfferDeclinedNotification($application, $offer, $message, $url));
                            Log::info('Notification sent to user', ['user_id' => $u->id, 'offer_id' => $offer->id]);
                        } catch (\Throwable $e) {
                            Log::error('Failed to notify user ' . $u->id, ['error' => $e->getMessage()]);
                        }
                    }
                } else {
                    Log::warning('No HR user ids found for company', ['company_id' => $companyId]);
                }
            } else {
                Log::warning('No company associated with application when notifying HR', ['application_id' => $application->id ?? null]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to notify HR on offer accept/decline: ' . $e->getMessage());
        }

        return back()->with('success', 'Offer declined.');
    }
}