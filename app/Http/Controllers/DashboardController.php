<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Sector;
use App\Models\User;
use App\Models\Graduate;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $summary = [
            'total_jobs' => 0,
            'total_applications' => 0,
            'total_hires' => 0,
        ];
/** @var \App\Models\User $user */
        
        // If user is institution, provide graduate stats
        if ($user->hasRole('institution')) {
            $institutionId = $user->id; // or $user->institution_id if that's your setup

            $summary = [
                'total_graduates' => Graduate::where('institution_id', $institutionId)->count(),
                'employed' => Graduate::where('institution_id', $institutionId)->where('employment_status', 'employed')->count(),
                'underemployed' => Graduate::where('institution_id', $institutionId)->where('employment_status', 'underemployed')->count(),
                'unemployed' => Graduate::where('institution_id', $institutionId)->where('employment_status', 'unemployed')->count(),
            ];

            $graduates = Graduate::where('institution_id', $institutionId)->get();
            $programs = Program::where('institution_id', $institutionId)->get();
            $careerOpportunities = Graduate::where('institution_id', $institutionId)
                ->whereNotNull('current_job_title')
                ->where('current_job_title', '!=', 'N/A')
                ->pluck('current_job_title')
                ->unique()
                ->values();
        }

        return Inertia::render('Dashboard', [
            'userNotApproved' => !$user->is_approved,
            // 'roles' => [
            //     'isCompany' => $user->hasRole('company'),
            //     'isInstitution' => $user->hasRole('institution'),
            // ],
            'summary' => $summary,
            'graduates' => $graduates ?? [],
            'programs' => $programs ?? [],
            'careerOpportunities' => $careerOpportunities ?? [],
        ]);
    }
}


