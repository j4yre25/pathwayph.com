<?php

namespace App\Http\Controllers\Institution;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class InstitutionReportsController extends Controller
{
    public function index()
    {
        return Inertia::render('Institutions/Reports/Index');
    }

    public function schoolYear()
    {
        return Inertia::render('Institutions/Reports/SchoolYearReport');
    }

    public function degree()
    {
        return Inertia::render('Institutions/Reports/DegreeReport');
    }

    public function programs()
    {
        return Inertia::render('Institutions/Reports/ProgramsReport');
    }

    public function career()
    {
        return Inertia::render('Institutions/Reports/CareerReport');
    }

    public function skill()
    {
        return Inertia::render('Institutions/Reports/SkillReport');
    }
    public function graduate()
    {
        $user = auth()->user();
        $institution = $user->institution;

        // Graduates with relations
        $graduates = \App\Models\Graduate::with([
            'program.degree',
            'schoolYear',
            'graduateSkills.skill',
            'careerGoals',
            'internshipPrograms',
        ])
            ->where('institution_id', $institution->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // All school years for this institution (not just those present in graduates)
        $schoolYears = \App\Models\InstitutionSchoolYear::with('schoolYear')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->schoolYear->id,
                    'school_year_range' => $item->schoolYear->school_year_range,
                    'term' => $item->term,
                ];
            });

        // Degrees and programs present in graduates
        $degrees = \App\Models\Degree::whereIn('id', $graduates->pluck('program.degree.id')->unique())->get();
        $programs = \App\Models\Program::whereIn('id', $graduates->pluck('program_id')->unique())->get();

        // All institution career opportunities (not just those assigned to graduates)
        $institutionCareerOpportunities = \App\Models\InstitutionCareerOpportunity::with('careerOpportunity')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(fn($ico) => [
                'id' => $ico->careerOpportunity->id,
                'title' => $ico->careerOpportunity->title,
            ])
            ->unique('id')
            ->values();

        // All institution skills (not just those assigned to graduates)
        $institutionSkills = \App\Models\InstitutionSkill::with('skill')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(fn($is) => [
                'id' => $is->skill->id,
                'name' => $is->skill->name,
            ])
            ->unique('id')
            ->values();

        // All internship programs for this institution
        $internshipPrograms = \App\Models\InternshipProgram::where('institution_id', $institution->id)->get();

        return Inertia::render('Institutions/Reports/GraduateReports', [
            'graduates' => $graduates,
            'schoolYears' => $schoolYears,
            'degrees' => $degrees,
            'programs' => $programs,
            'careerOpportunities' => $institutionCareerOpportunities,
            'skills' => $institutionSkills,
            'internshipPrograms' => $internshipPrograms,
        ]);
    }
}