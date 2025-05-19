<?php

namespace App\Http\Controllers;

use App\Models\Graduate;
use App\Models\InstitutionProgram;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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

        $graduates = [];
        $programs = [];
        $careerOpportunities = [];

        if ($user->hasRole('institution')) {
            $institutionId = $user->id;

            $summary = [
                'total_graduates' => Graduate::where('institution_id', $institutionId)->count(),
                'employed' => Graduate::where('institution_id', $institutionId)->where('employment_status', 'employed')->count(),
                'underemployed' => Graduate::where('institution_id', $institutionId)->where('employment_status', 'underemployed')->count(),
                'unemployed' => Graduate::where('institution_id', $institutionId)->where('employment_status', 'unemployed')->count(),
            ];

            // Get institution-specific programs
            $programs = InstitutionProgram::with('program')
                ->where('institution_id', $institutionId)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->program->id,
                        'name' => $item->program->name,
                        'code' => $item->program_code,
                        'degree' => $item->degree->type ?? null,
                    ];
                });

            // Graduates
            $graduates = Graduate::where('institution_id', $institutionId)->get();

            // Career Opportunities
            $careerOpportunities = $graduates
                ->whereNotNull('current_job_title')
                ->where('current_job_title', '!=', 'N/A')
                ->pluck('current_job_title')
                ->unique()
                ->values();
        }

        return Inertia::render('Dashboard', [
            'userNotApproved' => !$user->is_approved,
            'roles' => [
                'isCompany' => $user->hasRole('company'),
                'isInstitution' => $user->hasRole('institution'),
            ],
            'summary' => $summary,
            'graduates' => $graduates,
            'programs' => $programs,
            'careerOpportunities' => $careerOpportunities,
        ]);
    }
}
