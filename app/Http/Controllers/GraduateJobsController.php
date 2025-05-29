<?php

namespace App\Http\Controllers;

use App\Models\Job;
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

            // Skills
            foreach ($graduateSkills as $skill) {
                if (stripos(json_encode($job->skills), $skill) !== false) {
                    $labels[] = 'Skills';
                    break;
                }
            }

            // Education
            if ($program && stripos($job->job_requirements, $program) !== false) {
                $labels[] = 'Education';
            }

            // Experience
            foreach ($experienceTitles as $title) {
                if (stripos($job->job_title, $title) !== false) {
                    $labels[] = 'Experience';
                    break;
                }
            }

            // Employment Preferences
            if (in_array($job->job_type, $preferredJobTypes)) {
                $labels[] = 'Preferred Job Type';
            }
            if (in_array($job->location, $preferredLocations)) {
                $labels[] = 'Preferred Location';
            }
            if (in_array($job->work_environment, $preferredWorkEnvironments)) {
                $labels[] = 'Preferred Work Environment';
            }
            if ($minSalary && $job->job_min_salary >= $minSalary) {
                $labels[] = 'Preferred Min Salary';
            }
            if ($maxSalary && $job->job_max_salary <= $maxSalary) {
                $labels[] = 'Preferred Max Salary';
            }
            if ($salaryType && stripos($job->job_salary_type, $salaryType) !== false) {
                $labels[] = 'Preferred Salary Type';
            }

            // Past Keywords
            foreach ($pastKeywords as $keyword) {
                if (
                    stripos($job->job_title, $keyword) !== false ||
                    stripos($job->job_description, $keyword) !== false
                ) {
                    $labels[] = 'Past Search';
                    break;
                }
            }

            // Only include jobs with at least one label (i.e., a match)
            if (!empty($labels)) {
                $job->match_labels = array_unique($labels);
                $recommendations[] = $job;
            }
        }

        // Limit to 5 recommendations
        $recommendations = array_slice($recommendations, 0, 5);

        return response()->json(['recommendations' => $recommendations]);
    }

    public function oneClickApply(Request $request)
    {
        $user = Auth::user();
        $graduate = $user->graduate;

        // Get latest resume (assuming you have a Resume model)
        $resume = \App\Models\Resume::where('graduate_id', $graduate->id)->latest()->first();

        // Get default cover letter (assuming it's stored on the graduate profile)
        $coverLetter = $graduate->cover_letter ?? '';

        \App\Models\JobApplication::create([
            'graduate_id' => $graduate->id,
            'job_id' => $request->job_id,
            'status' => 'applied',
            'applied_at' => now(),
            'resume_id' => $resume ? $resume->id : null,
            'cover_letter' => $coverLetter,
        ]);

        return response()->json(['success' => true, 'message' => 'Applied with your latest resume and cover letter!']);
    }
}
