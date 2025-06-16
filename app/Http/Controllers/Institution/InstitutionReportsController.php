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
        $user = auth()->user();
        $institution = $user->institution;

        // All school years for this institution
        $schoolYears = \App\Models\InstitutionSchoolYear::with('schoolYear')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'school_year_id' => $item->schoolYear->id,
                    'school_year_range' => $item->schoolYear->school_year_range,
                    'term' => $item->term,
                    'status' => $item->deleted_at ? 'Inactive' : 'Active',
                ];
            });

        // Graduates with relations
        $graduates = \App\Models\Graduate::with([
            'program.degree',
            'schoolYear',
        ])
            ->where('institution_id', $institution->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($g) use ($institution) {
                // Get the term from institution_school_years
                $instSchoolYear = \App\Models\InstitutionSchoolYear::where('institution_id', $institution->id)
                    ->where('school_year_range_id', $g->school_year_id)
                    ->first();

                return [
                    'id' => $g->id,
                    'first_name' => $g->first_name,
                    'middle_name' => $g->middle_name,
                    'last_name' => $g->last_name,
                    'gender' => $g->gender, // <-- add this
                    'school_year_id' => $g->schoolYear?->id,
                    'school_year_range' => $g->schoolYear?->school_year_range,
                    'term' => $instSchoolYear?->term, // <-- fix here
                    'degree' => $g->program?->degree?->type,
                    'degree_id' => $g->program?->degree?->id,
                    'program' => $g->program?->name,
                    'program_id' => $g->program?->id,
                    'employment_status' => $g->employment_status,
                    'current_job_title' => $g->current_job_title,
                ];
            });

        // For filters
        $degrees = \App\Models\Degree::whereIn('id', $graduates->pluck('degree_id')->unique()->filter())->get();
        $programs = \App\Models\Program::whereIn('id', $graduates->pluck('program_id')->unique()->filter())->get();

        return Inertia::render('Institutions/Reports/SchoolYearReport', [
            'schoolYears' => $schoolYears,
            'graduates' => $graduates,
            'degrees' => $degrees,
            'programs' => $programs,
        ]);
    }

    public function degree()
    {
        $user = auth()->user();
        $institution = $user->institution;

        // All degrees for this institution
        $degrees = \App\Models\InstitutionDegree::with('degree')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'degree_id' => $item->degree->id,
                    'degree_code' => $item->degree_code,
                    'type' => $item->degree->type,
                    'status' => $item->deleted_at ? 'Inactive' : 'Active',
                ];
            });

        // Graduates with relations
        $graduates = \App\Models\Graduate::with([
            'program.degree',
            'schoolYear',
        ])
            ->where('institution_id', $institution->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($g) use ($institution) {
                $instSchoolYear = \App\Models\InstitutionSchoolYear::where('institution_id', $institution->id)
                    ->where('school_year_range_id', $g->school_year_id)
                    ->first();

                return [
                    'id' => $g->id,
                    'first_name' => $g->first_name,
                    'middle_name' => $g->middle_name,
                    'last_name' => $g->last_name,
                    'gender' => $g->gender, // <-- add this
                    'school_year_id' => $g->schoolYear?->id,
                    'school_year_range' => $g->schoolYear?->school_year_range,
                    'term' => $instSchoolYear?->term, // <-- updated here
                    'degree' => $g->program?->degree?->type,
                    'degree_id' => $g->program?->degree?->id,
                    'program' => $g->program?->name,
                    'program_id' => $g->program?->id,
                    'employment_status' => $g->employment_status,
                    'current_job_title' => $g->current_job_title,
                ];
            });

        // For filters
        $schoolYears = \App\Models\InstitutionSchoolYear::with('schoolYear')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(fn($item) => [
                'id' => $item->schoolYear->id,
                'school_year_range' => $item->schoolYear->school_year_range,
            ]);
        $programs = \App\Models\Program::whereIn('id', $graduates->pluck('program_id')->unique()->filter())->get();

        return Inertia::render('Institutions/Reports/DegreeReport', [
            'degrees' => $degrees,
            'graduates' => $graduates,
            'schoolYears' => $schoolYears,
            'programs' => $programs,
        ]);
    }

    public function programs()
    {
        $user = auth()->user();
        $institution = $user->institution;

        // All programs for this institution (with degree)
        $programs = \App\Models\InstitutionProgram::with('program.degree')
            ->where('institution_id', $institution->id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'program_id' => $item->program->id,
                    'program_code' => $item->program_code,
                    'name' => $item->program->name,
                    'degree' => $item->program->degree?->type,
                    'degree_id' => $item->program->degree?->id,
                    'status' => $item->deleted_at ? 'Inactive' : 'Active',
                ];
            });

        // Graduates with relations
        $graduates = \App\Models\Graduate::with([
            'program.degree',
            'schoolYear',
        ])
            ->where('institution_id', $institution->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($g) use ($institution) {
                $instSchoolYear = \App\Models\InstitutionSchoolYear::where('institution_id', $institution->id)
                    ->where('school_year_range_id', $g->school_year_id)
                    ->first();

                return [
                    'id' => $g->id,
                    'first_name' => $g->first_name,
                    'middle_name' => $g->middle_name,
                    'last_name' => $g->last_name,
                    'gender' => $g->gender, // <-- add this
                    'school_year_id' => $g->schoolYear?->id,
                    'school_year_range' => $g->schoolYear?->school_year_range,
                    'term' => $instSchoolYear?->term, // <-- updated here
                    'degree' => $g->program?->degree?->type,
                    'degree_id' => $g->program?->degree?->id,
                    'program' => $g->program?->name,
                    'program_id' => $g->program?->id,
                    'employment_status' => $g->employment_status,
                    'current_job_title' => $g->current_job_title,
                ];
            });

        // For filters
        $schoolYears = \App\Models\InstitutionSchoolYear::with('schoolYear')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(fn($item) => [
                'id' => $item->schoolYear->id,
                'school_year_range' => $item->schoolYear->school_year_range,
            ]);
        $degrees = \App\Models\Degree::whereIn('id', $graduates->pluck('degree_id')->unique()->filter())->get();
        $programOptions = \App\Models\Program::whereIn('id', $graduates->pluck('program_id')->unique()->filter())->get();

        return Inertia::render('Institutions/Reports/ProgramsReport', [
            'programs' => $programs,
            'graduates' => $graduates,
            'schoolYears' => $schoolYears,
            'degrees' => $degrees,
            'programOptions' => $programOptions,
        ]);
    }

    public function career()
    {
        $user = auth()->user();
        $institution = $user->institution;

        // All institution career opportunities (with degree and program, avoid duplicates)
        $careerOpportunities = \App\Models\InstitutionCareerOpportunity::with(['careerOpportunity', 'program.degree'])
            ->where('institution_id', $institution->id)
            ->whereNull('deleted_at')
            ->get()
            ->unique(function ($item) {
                return $item->career_opportunity_id . '-' . $item->program_id;
            })
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'degree' => $item->program?->degree?->type,
                    'program' => $item->program?->name,
                    'career_opportunity' => $item->careerOpportunity?->title,
                ];
            })
            ->values();

        // Graduates with relations
        $graduates = \App\Models\Graduate::with([
            'program.degree',
            'schoolYear',
            'careerGoals.careerOpportunity',
        ])
            ->where('institution_id', $institution->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($g) use ($institution) {
                $instSchoolYear = \App\Models\InstitutionSchoolYear::where('institution_id', $institution->id)
                    ->where('school_year_range_id', $g->school_year_id)
                    ->first();

                return [
                    'id' => $g->id,
                    'first_name' => $g->first_name,
                    'middle_name' => $g->middle_name,
                    'last_name' => $g->last_name,
                    'gender' => $g->gender, // <-- add this
                    'school_year_id' => $g->schoolYear?->id,
                    'school_year_range' => $g->schoolYear?->school_year_range,
                    'term' => $instSchoolYear?->term, // <-- updated here
                    'degree' => $g->program?->degree?->type,
                    'degree_id' => $g->program?->degree?->id,
                    'program' => $g->program?->name,
                    'program_id' => $g->program?->id,
                    'career_opportunity' => $g->careerGoals?->careerOpportunity?->title,
                    'employment_status' => $g->employment_status,
                    'current_job_title' => $g->current_job_title,
                ];
            });

        // For filters
        $schoolYears = \App\Models\InstitutionSchoolYear::with('schoolYear')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(fn($item) => [
                'id' => $item->schoolYear->id,
                'school_year_range' => $item->schoolYear->school_year_range,
            ]);
        $degrees = \App\Models\Degree::whereIn('id', $graduates->pluck('degree_id')->unique()->filter())->get();
        $programs = \App\Models\Program::whereIn('id', $graduates->pluck('program_id')->unique()->filter())->get();

        return Inertia::render('Institutions/Reports/CareerReport', [
            'careerOpportunities' => $careerOpportunities,
            'graduates' => $graduates,
            'schoolYears' => $schoolYears,
            'degrees' => $degrees,
            'programs' => $programs,
        ]);
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
            ->get()
            ->map(function ($g) use ($institution) {
                // Find the institution_school_year entry for this graduate's school_year_id and institution
                $instSchoolYear = \App\Models\InstitutionSchoolYear::where('institution_id', $institution->id)
                    ->where('school_year_range_id', $g->school_year_id)
                    ->first();

                return [
                    'id' => $g->id,
                    'first_name' => $g->first_name,
                    'middle_name' => $g->middle_name,
                    'last_name' => $g->last_name,
                    'gender' => $g->gender, // <-- add this
                    'school_year_id' => $g->schoolYear?->id,
                    'school_year_range' => $g->schoolYear?->school_year_range,
                    'term' => $instSchoolYear?->term, // <-- get term from institution_school_years
                    'degree' => $g->program?->degree?->type,
                    'degree_id' => $g->program?->degree?->id,
                    'program' => $g->program?->name,
                    'program_id' => $g->program?->id,
                    'employment_status' => $g->employment_status,
                    'current_job_title' => $g->current_job_title,
                ];
            });

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

        // Collect all unique terms for the filter
        $terms = $schoolYears->pluck('term')->unique()->filter()->values();

        // Degrees and programs present in graduates
        $degrees = \App\Models\InstitutionDegree::with('degree')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(fn($item) => [
                'id' => $item->degree->id,
                'type' => $item->degree->type,
            ])
            ->unique('id')
            ->values();
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
            'terms' => $terms, // <-- add this
        ]);
    }
}