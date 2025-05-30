<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Graduate;
use App\Models\Program;
use App\Models\Job;
use App\Models\GraduateSkill;
use App\Models\Skill;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PesoReportsController extends Controller
{
public function reports(Request $request)
{
    // Employment Status Overview
        $graduates = Graduate::with('schoolYear', 'program')->get();

        $summary = [
            'total_graduates' => $graduates->count(),
            'employed' => $graduates->where('employment_status', 'Employed')->count(),
            'underemployed' => $graduates->where('employment_status', 'Underemployed')->count(),
            'unemployed' => $graduates->where('employment_status', 'Unemployed')->count(),
        ];

        $statusCounts = [
            'Employed' => $summary['employed'],
            'Underemployed' => $summary['underemployed'],
            'Unemployed' => $summary['unemployed'],
        ];

        // Employment By Program
        // Get all programs
        $programs = Program::all();

        $programNames = [];
        $employedByProgram = [];
        $unemployedByProgram = [];

        foreach ($programs as $program) {
            $programNames[] = $program->name;
            $employedByProgram[] = $graduates
                ->where('program_id', $program->id)
                ->where('employment_status', 'Employed')
                ->count();
            $unemployedByProgram[] = $graduates
                ->where('program_id', $program->id)
                ->where('employment_status', 'Unemployed')
                ->count();
        }

    // --- Skills and Roles Analysis ---

    // 1. Most common job roles among employed graduates (word cloud)
    $employedGraduates = Graduate::where('employment_status', 'Employed')->get();
    $jobRoles = $employedGraduates->pluck('job_role')->filter()->countBy();

    // 2. Most common skills among employed graduates (word cloud)
    $employedGraduateIds = $employedGraduates->pluck('id');
    $employedSkills = GraduateSkill::whereIn('graduate_id', $employedGraduateIds)->pluck('skill_id');
    $skillCounts = Skill::whereIn('id', $employedSkills)->pluck('name', 'id')->mapWithKeys(function ($name, $id) use ($employedSkills) {
        return [$name => $employedSkills->where('id', $id)->count()];
    });

    // 3. Skill demand from job postings (bar chart)
    $jobSkills = Job::where('is_approved', 1)->pluck('skills')->flatten()->filter();
    $jobSkillCounts = [];
    foreach ($jobSkills as $skillArr) {
        if (is_array($skillArr)) {
            foreach ($skillArr as $skillName) {
                $jobSkillCounts[$skillName] = ($jobSkillCounts[$skillName] ?? 0) + 1;
            }
        } elseif (is_string($skillArr)) {
            $jobSkillCounts[$skillArr] = ($jobSkillCounts[$skillArr] ?? 0) + 1;
        }
    }

    // 4. Skill supply from all graduates (bar chart)
    $allGraduateSkills = GraduateSkill::pluck('skill_id');
    $allSkillCounts = Skill::whereIn('id', $allGraduateSkills)->pluck('name', 'id')->mapWithKeys(function ($name, $id) use ($allGraduateSkills) {
        return [$name => $allGraduateSkills->where('id', $id)->count()];
    });

    // 5. Top skills (by demand and supply)
    $topSkillDemand = collect($jobSkillCounts)->sortDesc()->take(10);
    $topSkillSupply = $allSkillCounts->sortDesc()->take(10);

        return Inertia::render('Admin/Reports/Reports', [
            'summary' => $summary,
            'statusCounts' => $statusCounts,
            'programNames' => $programNames,
            'employedByProgram' => $employedByProgram,
            'unemployedByProgram' => $unemployedByProgram,
            'jobRolesWordCloud' => $jobRoles,
            'skillsWordCloud' => $skillCounts,
            'topSkillDemand' => $topSkillDemand,
            'topSkillSupply' => $topSkillSupply,
        ]);
    }


    // public function employmentByProgram()
    // {
    //     // // Example: Get all programs and count employed/unemployed graduates per program
    //     // $programs = \App\Models\Program::withCount([
    //     //     'graduates as employed_count' => function ($q) {
    //     //         $q->whereHas('jobApplications', function($q2) {
    //     //             $q2->where('status', 'hired');
    //     //         });
    //     //     },
    //     //     'graduates as unemployed_count' => function ($q) {
    //     //         $q->whereDoesntHave('jobApplications', function($q2) {
    //     //             $q2->where('status', 'hired');
    //     //         });
    //     //     }
    //     // ])->get();

    //     // // Prepare data for chart
    //     // $programNames = $programs->pluck('name');
    //     // $employed = $programs->pluck('employed_count');
    //     // $unemployed = $programs->pluck('unemployed_count');

    //     $programNames = ['BSIT', 'BSBA', 'BSED', 'BSN'];
    //     $employed = [120, 80, 60, 90];
    //     $unemployed = [30, 40, 20, 10];


    //     return Inertia::render('Admin/Reports/Reports', [
    //         // ...other props
    //         'programNames' => $programNames,
    //         'employedByProgram' => $employed,
    //         'unemployedByProgram' => $unemployed,
    //     ]);
    // }
}
