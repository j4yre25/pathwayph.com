<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GraduateJobsController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        $employmentPreferences = null;

        if ($user && $user->role === 'graduate') {
            $employmentPreferences = $user->employmentPreference;
        }

        // Fetch filter options
        $industries = \App\Models\Sector::select('id', 'name')->get();
        $companies = \App\Models\Company::select('id', 'company_name')->get();
        $jobTypes = \App\Models\JobType::pluck('type', 'id');
        $locations = \App\Models\Location::pluck('address', 'id');
        $experienceLevels = Job::distinct()->pluck('job_experience_level');

        // Fetch jobs with relationships for poster info
        $jobs = Job::with(['company', 'institution', 'peso', 'sector', 'category', 'jobTypes', 'locations', 'salary'])
            ->where('status', 'open')
            ->where('is_approved', 1)
            ->when($request->keywords, function ($query, $keywords) {
                $query->where(function ($q) use ($keywords) {
                    $q->where('job_title', 'like', "%{$keywords}%")
                        ->orWhere('job_description', 'like', "%{$keywords}%");
                });
            })
            ->when($request->jobType, function ($q, $type) {
                $q->whereHas('jobTypes', fn($sub) => $sub->where('job_type_id', $type));
            })
            ->when($request->location, function ($q, $location) {
                $q->whereHas('locations', fn($sub) => $sub->where('location_id', $location));
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


        // Clean skills field before sending to Inertia
        $jobs->getCollection()->transform(function ($job) {
            // Decode JSON skills if stored as string, or use as is if array
            $skillsArray = is_string($job->skills) ? json_decode($job->skills, true) : ($job->skills ?? []);

            // Clean each skill
            $cleanSkills = array_map(function ($skill) {
                $skill = trim($skill);
                // Remove spaces inside the skill (turn "P y t h o n" into "Python")
                $skill = str_replace(' ', '', $skill);
                return $skill;
            }, $skillsArray);

            // Assign back clean skills
            $job->skills = $cleanSkills;

            return $job;
        });

        return Inertia::render('Frontend/JobSearch', [
            'jobs' => $jobs,
            'industries' => $industries,
            'companies' => $companies,
            'jobTypes' => $jobTypes,
            'locations' => $locations,
            'experienceLevels' => $experienceLevels,
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
        $locations = \App\Models\Location::pluck('address', 'id');
        $experienceLevels = Job::distinct()->pluck('job_experience_level');

        // Fetch jobs with relationships for poster info (same as index)
        $jobs = Job::with(['company', 'institution', 'peso', 'sector', 'category', 'jobTypes', 'locations', 'salary'])
            ->where('status', 'open')
            ->where('is_approved', 1)
            ->when($request->keywords, function ($query, $keywords) {
                $query->where(function ($q) use ($keywords) {
                    $q->where('job_title', 'like', "%{$keywords}%")
                        ->orWhere('job_description', 'like', "%{$keywords}%");
                });
            })
            ->when($request->jobType, function ($q, $type) {
                $q->whereHas('jobTypes', fn($sub) => $sub->where('job_type_id', $type));
            })
            ->when($request->location, function ($q, $location) {
                $q->whereHas('locations', fn($sub) => $sub->where('location_id', $location));
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

        // Clean skills field before sending to Inertia
        $jobs->getCollection()->transform(function ($job) {
            // Decode JSON skills if stored as string, or use as is if array
            $skillsArray = is_string($job->skills) ? json_decode($job->skills, true) : ($job->skills ?? []);

            // Clean each skill
            $cleanSkills = array_map(function ($skill) {
                $skill = trim($skill);
                // Remove spaces inside the skill (turn "P y t h o n" into "Python")
                return $skill;
            }, $skillsArray);

            // Assign back clean skills
            $job->skills = $cleanSkills;

            return $job;
        });

        return Inertia::render('Frontend/JobSearch', [
            'jobs' => $jobs,
            'industries' => $industries,
            'companies' => $companies,
            'jobTypes' => $jobTypes,
            'locations' => $locations,
            'experienceLevels' => $experienceLevels,
        ]);
    }


    public function recommendations()
    {
        $user = Auth::user();
        $graduate = $user->graduate;

        // Get graduate's skills (array of skill names)
        $graduateSkills = \App\Models\GraduateSkill::where('graduate_id', $graduate->id)
            ->with('skill')
            ->get()
            ->pluck('skill.name')
            ->filter()
            ->unique()
            ->toArray();

        // Get graduate's education (e.g., program or degree)
        $education = \App\Models\Education::where('graduate_id', $graduate->id)->first();
        $program = $education ? $education->program : null;

        // Get graduate's experience (e.g., job titles)
        $experiences = \App\Models\Experience::where('graduate_id', $graduate->id)->get();
        $experienceTitles = $experiences->pluck('job_title')->filter()->unique()->toArray();

        // Get employment preferences
        $preferences = \App\Models\EmploymentPreference::where('graduate_id', $graduate->id)->first();
        $preferredJobTypes = $preferences && $preferences->job_type ? explode(',', $preferences->job_type) : [];
        $preferredLocations = $preferences && $preferences->location ? explode(',', $preferences->location) : [];
        $preferredWorkEnvironments = $preferences && $preferences->work_environment ? explode(',', $preferences->work_environment) : [];
        $minSalary = $preferences && $preferences->employment_min_salary ? $preferences->employment_min_salary : null;
        $maxSalary = $preferences && $preferences->employment_max_salary ? $preferences->employment_max_salary : null;
        $salaryType = $preferences && $preferences->salary_type ? $preferences->salary_type : null;

        $pastKeywords = \App\Models\JobSearchHistory::where('graduate_id', $graduate->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->pluck('keywords')
            ->unique()
            ->toArray();

        // Build the recommendation query
        $jobs = Job::with(['company', 'jobTypes', 'locations', 'salary'])->get();

        $recommendations = [];

        foreach ($jobs as $job) {
            $labels = [];
            $score = 0;
            $criteria = 0;

            // Skills
            $criteria++;
            $skillMatch = false;
            foreach ($graduateSkills as $skill) {
                if (stripos(json_encode($job->skills), $skill) !== false) {
                    $labels[] = 'Skills';
                    $skillMatch = true;
                    break;
                }
            }
            if ($skillMatch) $score++;

            // Education
            $criteria++;
            $educationMatch = $program && stripos($job->job_requirements, $program) !== false;
            if ($educationMatch) {
                $labels[] = 'Education';
                $score++;
            }

            // Experience
            $criteria++;
            $experienceMatch = false;
            foreach ($experienceTitles as $title) {
                if (stripos($job->job_title, $title) !== false) {
                    $labels[] = 'Experience';
                    $experienceMatch = true;
                    break;
                }
            }
            if ($experienceMatch) $score++;

            // Preferred Job Type
            $criteria++;
            $jobTypeMatch = in_array($job->job_type, $preferredJobTypes);
            if ($jobTypeMatch) {
                $labels[] = 'Preferred Job Type';
                $score++;
            }

            // Preferred Location
            $criteria++;
            $locationMatch = in_array($job->location, $preferredLocations);
            if ($locationMatch) {
                $labels[] = 'Preferred Location';
                $score++;
            }

            // Preferred Work Environment
            $criteria++;
            $workEnvMatch = in_array($job->work_environment, $preferredWorkEnvironments);
            if ($workEnvMatch) {
                $labels[] = 'Preferred Work Environment';
                $score++;
            }

            // Preferred Min Salary
            $criteria++;
            $minSalaryMatch = $minSalary && $job->job_min_salary >= $minSalary;
            if ($minSalaryMatch) {
                $labels[] = 'Preferred Min Salary';
                $score++;
            }

            // Preferred Max Salary
            $criteria++;
            $maxSalaryMatch = $maxSalary && $job->job_max_salary <= $maxSalary;
            if ($maxSalaryMatch) {
                $labels[] = 'Preferred Max Salary';
                $score++;
            }

            // Preferred Salary Type
            $criteria++;
            $salaryTypeMatch = $salaryType && stripos($job->job_salary_type, $salaryType) !== false;
            if ($salaryTypeMatch) {
                $labels[] = 'Preferred Salary Type';
                $score++;
            }

            // Past Search Keywords
            $criteria++;
            $pastKeywordMatch = false;
            foreach ($pastKeywords as $keyword) {
                if (
                    stripos($job->job_title, $keyword) !== false ||
                    stripos($job->job_description, $keyword) !== false
                ) {
                    $labels[] = 'Past Search';
                    $pastKeywordMatch = true;
                    break;
                }
            }
            if ($pastKeywordMatch) $score++;

            // Only include jobs with at least one label (i.e., a match)
            if (!empty($labels)) {
                $job->match_labels = array_unique($labels);
                $job->match_score = $score;
                $job->match_percentage = round(($score / $criteria) * 100);
                $recommendations[] = $job;
            }
        }

        // Sort by match score descending
        usort($recommendations, fn($a, $b) => $b->match_score <=> $a->match_score);

        // Limit to 5 recommendations
        $recommendations = array_slice($recommendations, 0, 5);

        return response()->json(['recommendations' => $recommendations]);
    }

    public function oneClickApply(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        $user = Auth::user();
        $graduate = $user->graduate;

        // Get latest resume 
        $resume = \App\Models\Resume::where('graduate_id', $graduate->id)->latest()->first();
        $coverLetter = $graduate->cover_letter ?? '';

        $application = \App\Models\JobApplication::create([
            'user_id' => auth()->id(),
            'graduate_id' => $graduate->id,
            'job_id' => $request->job_id,
            'status' => 'applied',
            'stage' => 'screening',
            'applied_at' => now(),
            'resume_id' => $resume ? $resume->id : null,
            'cover_letter' => $coverLetter,
        ]);

        // Eager load relationships for screening
        $application->load([
            'graduate.education',
            'graduate.experience',
            'graduate.graduateSkills.skill',
            'graduate.employmentPreference'
        ]);

        $job = $application->job;
        $graduate = $application->graduate;

        // --- Screening logic inspired by recommendations() ---
        $labels = [];
        $score = 0;
        $criteria = 0;

        // 1. Skills
        $criteria++;
        $graduateSkills = $graduate->graduateSkills->pluck('skill.name')->filter()->unique()->toArray();
        $skillMatch = false;
        foreach ($graduateSkills as $skill) {
            if (stripos(json_encode($job->skills), $skill) !== false) {
                $labels[] = 'Skills';
                $skillMatch = true;
                break;
            }
        }
        if ($skillMatch) $score++;

        // 2. Education
        $criteria++;
        $education = $graduate->education->first();
        $program = $education ? $education->program : null;
        $educationMatch = $program && stripos($job->job_requirements, $program) !== false;
        if ($educationMatch) {
            $labels[] = 'Education';
            $score++;
        }

        // 3. Experience
        $criteria++;
        $experiences = $graduate->experience;
        $experienceTitles = $experiences->pluck('job_title')->filter()->unique()->toArray();
        $experienceMatch = false;
        foreach ($experienceTitles as $title) {
            if (stripos($job->job_title, $title) !== false) {
                $labels[] = 'Experience';
                $experienceMatch = true;
                break;
            }
        }
        if ($experienceMatch) $score++;

        // 4. Preferred Job Type
        $criteria++;
        $pref = $graduate->employmentPreference;
        $preferredJobTypes = $pref && $pref->job_type ? explode(',', $pref->job_type) : [];
        $jobTypeMatch = $job->job_type && in_array($job->job_type, $preferredJobTypes);
        if ($jobTypeMatch) {
            $labels[] = 'Preferred Job Type';
            $score++;
        }

        // 5. Preferred Location
        $criteria++;
        $preferredLocations = $pref && $pref->location ? explode(',', $pref->location) : [];
        $locationMatch = $job->location && in_array($job->location, $preferredLocations);
        if ($locationMatch) {
            $labels[] = 'Preferred Location';
            $score++;
        }

        // 6. Preferred Work Environment
        $criteria++;
        $preferredWorkEnvironments = $pref && $pref->work_environment ? explode(',', $pref->work_environment) : [];
        $workEnvMatch = $job->work_environment && in_array($job->work_environment, $preferredWorkEnvironments);
        if ($workEnvMatch) {
            $labels[] = 'Preferred Work Environment';
            $score++;
        }

        // 7. Preferred Min Salary
        $criteria++;
        $minSalary = $pref && $pref->employment_min_salary ? $pref->employment_min_salary : null;
        $minSalaryMatch = $minSalary && $job->job_min_salary >= $minSalary;
        if ($minSalaryMatch) {
            $labels[] = 'Preferred Min Salary';
            $score++;
        }

        // 8. Preferred Max Salary
        $criteria++;
        $maxSalary = $pref && $pref->employment_max_salary ? $pref->employment_max_salary : null;
        $maxSalaryMatch = $maxSalary && $job->job_max_salary <= $maxSalary;
        if ($maxSalaryMatch) {
            $labels[] = 'Preferred Max Salary';
            $score++;
        }

        // 9. Preferred Salary Type
        $criteria++;
        $salaryType = $pref && $pref->salary_type ? $pref->salary_type : null;
        $salaryTypeMatch = $salaryType && stripos($job->job_salary_type, $salaryType) !== false;
        if ($salaryTypeMatch) {
            $labels[] = 'Preferred Salary Type';
            $score++;
        }

        // 10. Past Search Keywords (optional, can skip if not needed)
        // $criteria++;
        // $pastKeywords = ... (see recommendations() if you want to include this)

        // Calculate match percentage
        $matchPercentage = $criteria > 0 ? round(($score / $criteria) * 100) : 0;

        // Screening decision
        if ($matchPercentage > 30) {
            $application->is_shortlisted = true;
            $application->status = 'shortlisted';
            $application->stage = 'Screening';
            $application->screening_feedback = 'Auto-screened: Qualified (' . $matchPercentage . '% match)';
        } else {
            $application->is_shortlisted = false;
            $application->screening_feedback = 'Auto-screened: Not qualified (' . $matchPercentage . '% match)';
            $application->status = 'rejected';
        }
        $application->save();

        // Notify applicant if rejected
        if (in_array($application->status, ['rejected', 'shortlisted']) && $graduate->user) {
            $graduate->user->notify(new \App\Notifications\ApplicationStatusUpdated($application, $application->status));
        }

        return response()->json(['success' => true, 'message' => 'Applied with your latest resume and cover letter!']);
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
            'additional_documents' => 'nullable|array',
            // add other fields kung unsa pa ang need (check ang table named job_applications sa database)
        ]);

        // Check if user already applied for this job
        $exists = JobApplication::where('user_id', $user->id)
            ->where('job_id', $validated['job_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied to this job.'
            ], 409);
        }

        // Create application
        $application = JobApplication::create([
            'user_id' => $user->id,
            'graduate_id' => $graduate->id,
            'job_id' => $validated['job_id'],
            'resume_id' => $validated['resume_id'] ?? null,
            'cover_letter' => $validated['cover_letter'] ?? null,
            'additional_documents' => $validated['additional_documents'] ?? null,
            'status' => 'applied',
            'stage' => 'applying',
            'applied_at' => now(),
        ]);

        // --- Automated Screening Logic ---
        $job = $application->job;

        // 1. Retrieve job requirements (example fields, adjust as needed)
        $requiredDegree = $job->required_degree ?? 'Bachelor';
        $minExperience = $job->min_experience ?? 2; // years
        $requiredSkills = $job->required_skills ?? ['PHP', 'Vue.js']; // array or comma-separated

        // 2. Calculate applicant's total experience (in years)
        $totalExperience = $graduate->experience->sum(function($exp) {
            $start = $exp->start_date ? \Carbon\Carbon::parse($exp->start_date) : null;
            $end = $exp->end_date ? \Carbon\Carbon::parse($exp->end_date) : now();
            return $start && $end ? $start->diffInYears($end) : 0;
        });

        // 3. Get applicant's skills
        $skills = $graduate->graduateSkills->pluck('skill.name')->map(fn($s) => strtolower($s))->toArray();

        // 4. Check applicant's education
        $hasDegree = $graduate->education->contains(function($edu) use ($requiredDegree) {
            return stripos($edu->education, $requiredDegree) !== false;
        });

        // 5. Check if all criteria are met
        $hasSkills = collect($requiredSkills)->every(fn($skill) => in_array(strtolower($skill), $skills));
        $isQualified = $hasDegree && $totalExperience >= $minExperience && $hasSkills;

        if ($isQualified) {
            $application->is_shortlisted = true;
            $application->status = 'shortlisted';
            $application->stage = 'Screened';
            $application->screening_feedback = 'Auto-screened: Qualified';
        } else {
            $application->is_shortlisted = false;
            $application->screening_feedback = 'Auto-screened: Not qualified';
            // Optionally set status to 'rejected'
            // $application->status = 'rejected';
        }
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully.',
            'application' => $application,
        ]);
    }

    public function requestReferral(Request $request)
    {
        $user = auth()->user();
        $graduate = $user->graduate;

        \App\Models\JobInvitation::create([
            'graduate_id' => $graduate->id,
            'job_id' => $request->job_id,
            'company_id' => $request->company_id, // <-- add this line
            'status' => 'pending',
        ]);

        return back()->with('success', 'Referral request sent!');
    }
}
