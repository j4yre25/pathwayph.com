<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class JobSearchController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $recommendedJobs = collect([]);

        if ($user && $user->role === 'graduate') {
            $program = $user->graduate_program_completed;
            $yearGraduated = $user->graduate_year_graduated;
            $classification = $user->classification;

            $recommendedJobs = Job::query()
                ->when($program, function ($query, $program) {
                    return $query->where('required_qualification', 'like', "%{$program}%");
                })
                ->when($yearGraduated, function ($query, $yearGraduated) {
                    $yearsExperience = now()->year - $yearGraduated;
                    return $query->where('required_experience', '<=', $yearsExperience);
                })
                ->when($classification, function ($query, $classification) {
                    return $query->where('employment_type', $classification);
                })
                ->latest()
                ->take(10)
                ->get();
        }

        return Inertia::render('Frontend/JobSearch', [
            'recommendedJobs' => $recommendedJobs
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