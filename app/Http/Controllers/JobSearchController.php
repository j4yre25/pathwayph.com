<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class JobSearchController extends Controller
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

    // Fetch jobs with relationships for poster info
    $jobs = Job::with(['company', 'institution', 'peso', 'sector', 'category'])
        ->when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('job_title', 'like', "%{$search}%")
                  ->orWhere('job_location', 'like', "%{$search}%")
                  ->orWhere('job_description', 'like', "%{$search}%");
            });
        })
        ->when($request->jobType, fn($q, $type) => $q->where('job_employment_type', $type))
        ->when($request->location, fn($q, $location) => $q->where('job_location', 'like', "%{$location}%"))
        ->when($request->industry, fn($q, $industry) => $q->where('sector_id', $industry))
        ->when($request->salaryMin, fn($q, $min) => $q->where('job_min_salary', '>=', $min))
        ->when($request->salaryMax, fn($q, $max) => $q->where('job_max_salary', '<=', $max))
        ->when($request->skills, function ($q, $skills) {
            foreach ((array)$skills as $skill) {
                $q->whereJsonContains('related_skills', $skill);
            }
        })
        ->when($request->experience, fn($q, $exp) => $q->where('job_experience_level', $exp))
        ->when($request->company, fn($q, $company) => $q->where('company_id', $company))
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return Inertia::render('Frontend/JobSearch', [
        'employmentPreferences' => $employmentPreferences,
        'jobs' => $jobs,
        'industries' => $industries,
        'companies' => $companies,
    ]);
}
    public function search(Request $request)
    {
        $request->validate([
            'keywords' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $jobs = Job::query()
            ->when($request->keywords, function ($query, $keywords) {
                return $query->where(function ($q) use ($keywords) {
                    $q->where('title', 'like', "%{$keywords}%")
                      ->orWhere('description', 'like', "%{$keywords}%")
                      ->orWhere('required_qualification', 'like', "%{$keywords}%");
                });
            })
            ->when($request->employment_type, function ($query, $employment_type) {
                return $query->where('employment_type', $employment_type);
            })
            ->when($request->location, function ($query, $location) {
                return $query->where('location', 'like', "%{$location}%");
            })
            ->latest()
            ->paginate(10);

        return response()->json($jobs);
    }
} 