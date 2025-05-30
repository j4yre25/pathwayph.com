<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Graduate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PesoReportsController extends Controller
{
    public function employmentStatusOverview(Request $request)
    {
        // Count graduates with at least one 'hired' job application
        $employedCount = Graduate::whereHas('jobApplications', function ($q) {
            $q->where('status', 'hired');
        })->count();

        // Count graduates with NO 'hired' job application
        $unemployedCount = Graduate::whereDoesntHave('jobApplications', function ($q) {
            $q->where('status', 'hired');
        })->count();

        // Status breakdown (customize as needed)
        $statusCounts = [
            'Employed' => $employedCount,
            'Unemployed' => $unemployedCount,
        ];

        

        return Inertia::render('Admin/Reports/Reports', [
            'statusCounts' => $statusCounts,
            'employed' => $employedCount,
            'unemployed' => $unemployedCount,
        ]);
    }

    public function employmentByProgram()
    {
        // // Example: Get all programs and count employed/unemployed graduates per program
        // $programs = \App\Models\Program::withCount([
        //     'graduates as employed_count' => function ($q) {
        //         $q->whereHas('jobApplications', function($q2) {
        //             $q2->where('status', 'hired');
        //         });
        //     },
        //     'graduates as unemployed_count' => function ($q) {
        //         $q->whereDoesntHave('jobApplications', function($q2) {
        //             $q2->where('status', 'hired');
        //         });
        //     }
        // ])->get();

        // // Prepare data for chart
        // $programNames = $programs->pluck('name');
        // $employed = $programs->pluck('employed_count');
        // $unemployed = $programs->pluck('unemployed_count');

        $programNames = ['BSIT', 'BSBA', 'BSED', 'BSN'];
        $employed = [120, 80, 60, 90];
        $unemployed = [30, 40, 20, 10];


        return Inertia::render('Admin/Reports/Reports', [
            // ...other props
            'programNames' => $programNames,
            'employedByProgram' => $employed,
            'unemployedByProgram' => $unemployed,
        ]);
    }
}
