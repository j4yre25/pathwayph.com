<?php

namespace App\Http\Controllers\Institution;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;

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

    public function degree(Request $request)
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

        // School years and terms from InstitutionSchoolYear
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

        $terms = $schoolYears->pluck('term')->unique()->filter()->values();

        // Degrees and programs
        $degrees = \App\Models\InstitutionDegree::with('degree')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(fn($item) => [
                'id' => $item->degree->id,
                'type' => $item->degree->type,
            ])
            ->unique('id')
            ->values();

        $programs = \App\Models\InstitutionProgram::with('program')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(fn($ip) => [
                'id' => $ip->program->id,
                'name' => $ip->program->name,
            ])
            ->unique('id')
            ->values();

        // Career opportunities from InstitutionCareerOpportunity
        $careerOpportunities = \App\Models\InstitutionCareerOpportunity::with('careerOpportunity', 'program')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(fn($ico) => [
                'id' => $ico->careerOpportunity->id,
                'title' => $ico->careerOpportunity->title,
                'program_id' => $ico->program_id,
                'program_name' => $ico->program?->name,
            ])
            ->unique('id')
            ->values();

        // Skills and internship programs as before
        $institutionSkills = \App\Models\InstitutionSkill::with('skill')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(fn($is) => [
                'id' => $is->skill->id,
                'name' => $is->skill->name,
            ])
            ->unique('id')
            ->values();

        $internshipPrograms = \App\Models\InternshipProgram::where('institution_id', $institution->id)->get();

        return Inertia::render('Institutions/Reports/GraduateReports', [
            'schoolYears' => $schoolYears,
            'degrees' => $degrees,
            'programs' => $programs,
            'careerOpportunities' => $careerOpportunities,
            'skills' => $institutionSkills,
            'internshipPrograms' => $internshipPrograms,
            'terms' => $terms,
        ]);
    }

    public function graduateData(Request $request)
    {
        $user = auth()->user();
        $institution = $user->institution;

        $name = $request->input('name');
        $gender = $request->input('gender');
        $degree = $request->input('degree_id');
        $program = $request->input('program_id');
        $employmentStatus = $request->input('employment_status');
        $careerOpportunity = $request->input('career_opportunity');
        $skill = $request->input('skill');
        $internshipProgram = $request->input('internship_program_id');
        $term = $request->input('term');
        $alignment = $request->input('alignment');

        $graduatesQuery = \App\Models\Graduate::with([
            'program.degree',
            'institutionSchoolYear.schoolYear',
            'graduateSkills.skill',
            'careerGoals.careerOpportunity',
            'internshipPrograms',
        ])->where('institution_id', $institution->id);

        if ($name) {
            $graduatesQuery->where(function ($q) use ($name) {
                $q->where('first_name', 'like', "%$name%")
                    ->orWhere('middle_name', 'like', "%$name%")
                    ->orWhere('last_name', 'like', "%$name%");
            });
        }
        if ($gender)
            $graduatesQuery->where('gender', $gender);
        if ($degree)
            $graduatesQuery->whereHas('program.degree', fn($q) => $q->where('id', $degree));
        if ($program)
            $graduatesQuery->where('program_id', $program);
        if ($employmentStatus)
            $graduatesQuery->where('employment_status', $employmentStatus);
        if ($skill) {
            $graduatesQuery->whereHas('graduateSkills.skill', fn($q) => $q->where('name', $skill));
        }
        if ($internshipProgram) {
            $graduatesQuery->whereHas('internshipPrograms', fn($q) => $q->where('id', $internshipProgram));
        }

        // School Year and Term filter logic
        $schoolYearRange = $request->input('school_year_range');
        $term = $request->input('term');

        if ($schoolYearRange && $term) {
            $graduatesQuery->whereHas('institutionSchoolYear', function ($q) use ($schoolYearRange, $term) {
                $q->whereHas('schoolYear', function ($q2) use ($schoolYearRange) {
                    $q2->where('school_year_range', $schoolYearRange);
                })->where('term', $term);
            });
        } elseif ($schoolYearRange) {
            $graduatesQuery->whereHas('institutionSchoolYear', function ($q) use ($schoolYearRange) {
                $q->whereHas('schoolYear', function ($q2) use ($schoolYearRange) {
                    $q2->where('school_year_range', $schoolYearRange);
                });
            });
        } elseif ($term) {
            $graduatesQuery->whereHas('institutionSchoolYear', function ($q) use ($term) {
                $q->where('term', $term);
            });
        }

        // Career Opportunity filter: match current job title to selected career opportunity
        if ($careerOpportunity) {
            $graduatesQuery->whereRaw('LOWER(current_job_title) LIKE ?', ['%' . strtolower($careerOpportunity) . '%']);
        }

        $graduates = $graduatesQuery->get()->map(function ($g) use ($institution) {
            $instSchoolYear = $g->institutionSchoolYear; // Use InstitutionSchoolYear relationship

            // Alignment logic as before...
            $programCareerOpportunities = \App\Models\InstitutionCareerOpportunity::with('careerOpportunity')
                ->where('institution_id', $institution->id)
                ->where('program_id', $g->program_id)
                ->get()
                ->pluck('careerOpportunity.title')
                ->filter()
                ->map(fn($t) => strtolower(trim($t)));

            $currentJob = strtolower(trim($g->current_job_title ?? ''));
            $isAligned = false;
            if ($currentJob && $programCareerOpportunities->count()) {
                foreach ($programCareerOpportunities as $co) {
                    if ($co && str_contains($currentJob, $co)) {
                        $isAligned = true;
                        break;
                    }
                }
            }
            $isEmployedOrUnder = in_array($g->employment_status, ['Employed', 'Underemployed']);
            $alignmentStatus = ($isEmployedOrUnder && $isAligned) ? 'Aligned' : 'Misaligned';

            return [
                'id' => $g->id,
                'first_name' => $g->first_name,
                'middle_name' => $g->middle_name,
                'last_name' => $g->last_name,
                'gender' => $g->gender,
                'school_year_id' => $instSchoolYear?->schoolYear?->id,
                'school_year_range' => $instSchoolYear?->schoolYear?->school_year_range,
                'term' => $instSchoolYear?->term,
                'degree' => $g->program?->degree?->type,
                'degree_id' => $g->program?->degree?->id,
                'program' => $g->program?->name,
                'program_id' => $g->program?->id,
                'employment_status' => $g->employment_status,
                'current_job_title' => $g->current_job_title,
                'graduateSkills' => $g->graduateSkills,
                'careerGoals' => $g->careerGoals,
                'internshipPrograms' => $g->internshipPrograms,
                'alignment' => $alignmentStatus,
            ];
        });

        // Alignment filtering
        if ($alignment === 'Aligned') {
            $graduates = $graduates->where('alignment', 'Aligned')->values();
        } elseif ($alignment === 'Misaligned') {
            $graduates = $graduates->where('alignment', 'Misaligned')->values();
        }

        // For charting and summary
        $employed = $graduates->where('employment_status', 'Employed')->count();
        $underemployed = $graduates->where('employment_status', 'Underemployed')->count();
        $unemployed = $graduates->where('employment_status', 'Unemployed')->count();

        // Alignment counts
        $aligned = $graduates->where('alignment', 'Aligned')->count();
        $misaligned = $graduates->where('alignment', 'Misaligned')->count();

        return response()->json([
            'graduates' => $graduates,
            'employed' => $employed,
            'underemployed' => $underemployed,
            'unemployed' => $unemployed,
            'total' => $graduates->count(),
            'aligned' => $aligned,
            'misaligned' => $misaligned,
        ]);
    }

    public function schoolYearData(Request $request)
    {
        $user = auth()->user();
        $institution = $user->institution;

        $schoolYears = \App\Models\InstitutionSchoolYear::with('schoolYear')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'school_year_range' => $item->schoolYear->school_year_range ?? null,
                    'term' => $item->term,
                    'status' => $item->deleted_at ? 'Inactive' : 'Active', // <-- Use this for soft deletes
                ];
            });

        $graduates = \App\Models\Graduate::with([
            'program.degree',
            'schoolYear',
            'institutionSchoolYear.schoolYear', // <-- add this relationship
        ])
            ->where('institution_id', $institution->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($g) {
                // Get term and school_year_range from institutionSchoolYear
                $instSchoolYear = $g->institutionSchoolYear;
                return [
                    'id' => $g->id,
                    'first_name' => $g->first_name,
                    'middle_name' => $g->middle_name,
                    'last_name' => $g->last_name,
                    'school_year_range' => $instSchoolYear?->schoolYear?->school_year_range ?? null,
                    'term' => $instSchoolYear?->term ?? null,
                    'gender' => $g->gender,
                    'employment_status' => $g->employment_status,
                    'degree_id' => $g->program->degree->id ?? null,
                    'program_id' => $g->program_id,
                ];
            });

        $degrees = \App\Models\Degree::whereIn('id', $graduates->pluck('degree_id')->unique()->filter())->get();
        $programs = \App\Models\Program::whereIn('id', $graduates->pluck('program_id')->unique()->filter())->get();

        return response()->json([
            'schoolYears' => $schoolYears,
            'graduates' => $graduates,
            'degrees' => $degrees,
            'programs' => $programs,
        ]);

    }
    public function degreeData(Request $request)
    {
        $user = auth()->user();
        $institution = $user->institution;

        // Main filters
        $schoolYear = $request->input('schoolYear');
        $degreeId = $request->input('degree');
        $programId = $request->input('program');
        $sectorId = $request->input('sector');

        // For filter dropdowns
        $availableYears = \App\Models\SchoolYear::orderBy('school_year_range', 'desc')->pluck('school_year_range', 'id');
        $availableSectors = \App\Models\Sector::orderBy('name')->pluck('name', 'id');
        $availablePrograms = \App\Models\Program::orderBy('name')->pluck('name', 'id');

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
        $graduatesQuery = \App\Models\Graduate::with([
            'program.degree',
            'schoolYear',
        ])->where('institution_id', $institution->id);

        if ($schoolYear) {
            $graduatesQuery->whereHas('schoolYear', function ($q) use ($schoolYear) {
                $q->where('school_year_range', $schoolYear);
            });
        }
        if ($degreeId) {
            $graduatesQuery->whereHas('program.degree', function ($q) use ($degreeId) {
                $q->where('id', $degreeId);
            });
        }
        if ($programId) {
            $graduatesQuery->where('program_id', $programId);
        }

        $graduates = $graduatesQuery->orderBy('created_at', 'desc')->get()->map(function ($g) use ($institution) {
            $instSchoolYear = \App\Models\InstitutionSchoolYear::with('schoolYear')
                ->where('id', $g->school_year_id)
                ->first();

            return [
                'id' => $g->id,
                'first_name' => $g->first_name,
                'middle_name' => $g->middle_name,
                'last_name' => $g->last_name,
                'gender' => $g->gender,
                'school_year_id' => $g->schoolYear?->id,
                'school_year_range' => $g->schoolYear?->school_year_range,
                'term' => $instSchoolYear?->term,
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
        $programs = \App\Models\InstitutionProgram::with('program')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(fn($ip) => [
                'id' => $ip->program->id,
                'name' => $ip->program->name,
            ])
            ->unique('id')
            ->values();

        // --- Degree-to-Job Local Match Index logic ---
        $programsQuery = \App\Models\InstitutionProgram::with('program.degree')
            ->where('institution_id', $institution->id);

        if ($programId) {
            $programsQuery->where('program_id', $programId);
        }
        if ($degreeId) {
            $programsQuery->whereHas('program.degree', function ($q) use ($degreeId) {
                $q->where('id', $degreeId);
            });
        }

        $programsForMatch = $programsQuery->get();

        $results = [];

        foreach ($programsForMatch as $instProgram) {
            $program = $instProgram->program;
            if (!$program)
                continue;

            // Get all jobs mapped to this program
            $jobsQuery = \App\Models\Job::whereHas('programs', function ($q) use ($program) {
                $q->where('program_id', $program->id);
            })->whereNull('deleted_at');

            if ($sectorId) {
                $jobsQuery->where('sector_id', $sectorId);
            }

            $jobs = $jobsQuery->get();
            $jobTitles = $jobs->pluck('job_title')->map(fn($t) => strtolower(trim($t)))->unique();

            // Get all graduates for this program
            $graduatesQuery = \App\Models\Graduate::where('program_id', $program->id)
                ->where('institution_id', $institution->id)
                ->whereNull('deleted_at');

            if ($schoolYear) {
                $graduatesQuery->whereHas('schoolYear', function ($q) use ($schoolYear) {
                    $q->where('school_year_range', $schoolYear);
                });
            }
            if ($degreeId) {
                $graduatesQuery->whereHas('program.degree', function ($q) use ($degreeId) {
                    $q->where('id', $degreeId);
                });
            }

            $graduatesForMatch = $graduatesQuery->get();

            // Count matches
            $matchedGraduates = $graduatesForMatch->filter(function ($grad) use ($jobTitles) {
                return $grad->current_job_title && $jobTitles->contains(strtolower(trim($grad->current_job_title)));
            });

            $total = $graduatesForMatch->count();
            $matched = $matchedGraduates->count();
            $matchPercent = $total > 0 ? round(($matched / $total) * 100, 2) : 0;

            $results[] = [
                'program' => $program->name,
                'degree' => $program->degree?->type,
                'total_graduates' => $total,
                'matched_graduates' => $matched,
                'match_percentage' => $matchPercent,
                'program_id' => $program->id,
                'degree_id' => $program->degree?->id,
            ];
        }
        return response()->json([
            'degrees' => $degrees,
            'graduates' => $graduates,
            'schoolYears' => $schoolYears,
            'programs' => $programs,
            'results' => $results,
            'filters' => [
                'schoolYear' => $schoolYear,
                'degree' => $degreeId,
                'program' => $programId,
                'sector' => $sectorId,
            ],
            'availableYears' => $availableYears,
            'availableSectors' => $availableSectors,
            'availablePrograms' => $availablePrograms,
        ]);
    }

    public function programsData()
    {
        $user = auth()->user();
        $institution = $user->institution;

        $programs = \App\Models\InstitutionProgram::with('program.degree')
            ->where('institution_id', $institution->id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) use ($institution) {
                // Get all career opportunities for this program in this institution
                $careerOpportunities = \App\Models\InstitutionCareerOpportunity::with('careerOpportunity')
                    ->where('institution_id', $institution->id)
                    ->where('program_id', $item->program->id)
                    ->get()
                    ->pluck('careerOpportunity.title')
                    ->filter()
                    ->map(fn($t) => strtolower(trim($t)))
                    ->unique()
                    ->values();

                return [
                    'id' => $item->id,
                    'program_id' => $item->program->id,
                    'program_code' => $item->program_code,
                    'name' => $item->program->name,
                    'degree' => $item->program->degree?->type,
                    'degree_id' => $item->program->degree?->id,
                    'status' => $item->deleted_at ? 'Inactive' : 'Active',
                    'career_opportunities' => $careerOpportunities, // <-- add this
                ];
            });

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
                    'gender' => $g->gender,
                    'school_year_id' => $g->schoolYear?->id,
                    'school_year_range' => $g->schoolYear?->school_year_range,
                    'term' => $instSchoolYear?->term,
                    'degree' => $g->program?->degree?->type,
                    'degree_id' => $g->program?->degree?->id,
                    'program' => $g->program?->name,
                    'program_id' => $g->program?->id,
                    'employment_status' => $g->employment_status,
                    'current_job_title' => $g->current_job_title,
                ];
            });

        $schoolYears = \App\Models\InstitutionSchoolYear::with('schoolYear')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(fn($item) => [
                'id' => $item->schoolYear->id,
                'school_year_range' => $item->schoolYear->school_year_range,
            ]);
        $degrees = \App\Models\Degree::whereIn('id', $graduates->pluck('degree_id')->unique()->filter())->get();
        $programOptions = \App\Models\Program::whereIn('id', $graduates->pluck('program_id')->unique()->filter())->get();

        return response()->json([
            'programs' => $programs,
            'graduates' => $graduates,
            'schoolYears' => $schoolYears,
            'degrees' => $degrees,
            'programOptions' => $programOptions,
        ]);
    }

    public function careerData()
    {
        $user = auth()->user();
        $institution = $user->institution;

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

        $graduates = \App\Models\Graduate::with([
            'program.degree',
            'schoolYear',
            'careerGoals.careerOpportunity',
        ])
            ->where('institution_id', $institution->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($g) use ($institution) {
                $instSchoolYear = \App\Models\InstitutionSchoolYear::with('schoolYear')
                    ->where('id', $g->school_year_id)
                    ->first();

                return [
                    'id' => $g->id,
                    'first_name' => $g->first_name,
                    'middle_name' => $g->middle_name,
                    'last_name' => $g->last_name,
                    'gender' => $g->gender,
                    'school_year_id' => $g->schoolYear?->id,
                    'school_year_range' => $g->schoolYear?->school_year_range,
                    'term' => $instSchoolYear?->term,
                    'degree' => $g->program?->degree?->type,
                    'degree_id' => $g->program?->degree?->id,
                    'program' => $g->program?->name,
                    'program_id' => $g->program?->id,
                    'career_opportunity' => $g->careerGoals?->careerOpportunity?->title,
                    'employment_status' => $g->employment_status,
                    'current_job_title' => $g->current_job_title,
                ];
            });

        $schoolYears = \App\Models\InstitutionSchoolYear::with('schoolYear')
            ->where('institution_id', $institution->id)
            ->get()
            ->map(fn($item) => [
                'id' => $item->schoolYear->id,
                'school_year_range' => $item->schoolYear->school_year_range,
            ]);
        $degrees = \App\Models\Degree::whereIn('id', $graduates->pluck('degree_id')->unique()->filter())->get();
        $programs = \App\Models\Program::whereIn('id', $graduates->pluck('program_id')->unique()->filter())->get();

        return response()->json([
            'careerOpportunities' => $careerOpportunities,
            'graduates' => $graduates,
            'schoolYears' => $schoolYears,
            'degrees' => $degrees,
            'programs' => $programs,
        ]);
    }

}