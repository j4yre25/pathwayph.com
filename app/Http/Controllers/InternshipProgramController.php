<?php

namespace App\Http\Controllers;

use App\Models\InternshipProgram;
use App\Models\Graduate;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InternshipProgramController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->firstOrFail();

        $query = InternshipProgram::with(['programs', 'careerOpportunities', 'skills'])
            ->where('institution_id', $institution->id)
            ->when($request->program_id, fn($q) => $q->whereHas('programs', fn($q2) => $q2->where('program_id', $request->program_id)))
            ->when($request->career_opportunity_id, fn($q) => $q->whereHas('careerOpportunities', fn($q2) => $q2->where('career_opportunity_id', $request->career_opportunity_id)))
            ->when($request->skills, fn($q) => $q->whereHas('skills', fn($q2) => $q2->where('name', 'like', '%' . $request->skills . '%')))
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'active')
                    $q->where('is_active', true);
                if ($request->status === 'inactive')
                    $q->where('is_active', false);
            })
            ->withTrashed();

        $programs = $institution->institutionPrograms()->with('program')->get()->map(fn($ip) => $ip->program)->unique('id')->values();
        $careerOpportunities = $institution->institutionCareerOpportunities()->with('careerOpportunity')->get()->map(fn($ico) => $ico->careerOpportunity)->unique('id')->values();
        $skills = $institution->institutionSkills()->with('skill')->get()->map(fn($is) => $is->skill)->unique('id')->values();
        $graduates = Graduate::where('institution_id', $institution->id)->get();

        return Inertia::render('Institutions/InternshipPrograms/Index', [
            'internshipPrograms' => $query->get(),
            'programs' => $programs,
            'careerOpportunities' => $careerOpportunities,
            'skills' => $skills,
            'graduates' => $graduates,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->firstOrFail();

        $programs = $institution->institutionPrograms()->with('program')->get()->map(fn($ip) => $ip->program)->unique('id')->values();
        $careerOpportunities = $institution->institutionCareerOpportunities()->with('careerOpportunity')->get()->map(fn($ico) => $ico->careerOpportunity)->unique('id')->values();
        $skills = $institution->institutionSkills()->with('skill')->get()->map(fn($is) => $is->skill)->unique('id')->values();

        return Inertia::render('Institutions/InternshipPrograms/Create', [
            'programs' => $programs,
            'careerOpportunities' => $careerOpportunities,
            'skills' => $skills,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'program_id' => 'required|array|min:1',
            'program_id.*' => 'exists:programs,id',
            'career_opportunity_id' => 'required|array|min:1',
            'career_opportunity_id.*' => 'exists:career_opportunities,id',
            'skill_id' => 'required|array|min:1',
            'skill_id.*' => 'exists:skills,id',
        ]);

        $internship = InternshipProgram::create([
            'title' => $request->title,
            'institution_id' => $institution->id,
            'is_active' => true,
        ]);
        $internship->programs()->sync($request->program_id);
        $internship->careerOpportunities()->sync($request->career_opportunity_id);
        $internship->skills()->sync($request->skill_id);

        return back()->with('flash.banner', 'Internship program added.');
    }

    public function edit($id)
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->firstOrFail();
        $internship = InternshipProgram::with(['programs', 'careerOpportunities', 'skills'])->findOrFail($id);

        $programs = $institution->institutionPrograms()->with('program')->get()->map(fn($ip) => $ip->program)->unique('id')->values();
        $careerOpportunities = $institution->institutionCareerOpportunities()->with('careerOpportunity')->get()->map(fn($ico) => $ico->careerOpportunity)->unique('id')->values();
        $skills = $institution->institutionSkills()->with('skill')->get()->map(fn($is) => $is->skill)->unique('id')->values();

        return Inertia::render('Institutions/InternshipPrograms/Edit', [
            'internshipProgram' => $internship,
            'programs' => $programs,
            'careerOpportunities' => $careerOpportunities,
            'skills' => $skills,
        ]);
    }

    public function update(Request $request, $id)
    {
        $internship = InternshipProgram::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'program_id' => 'required|array|min:1',
            'program_id.*' => 'exists:programs,id',
            'career_opportunity_id' => 'required|array|min:1',
            'career_opportunity_id.*' => 'exists:career_opportunities,id',
            'skill_id' => 'required|array|min:1',
            'skill_id.*' => 'exists:skills,id',
        ]);

        $internship->update([
            'title' => $request->title,
        ]);
        $internship->programs()->sync($request->program_id);
        $internship->careerOpportunities()->sync($request->career_opportunity_id);
        $internship->skills()->sync($request->skill_id);

        return back()->with('flash.banner', 'Internship program updated.');
    }

    public function archive($id)
    {
        $program = InternshipProgram::findOrFail($id);
        $program->delete();
        return back()->with('flash.banner', 'Internship program archived.');
    }

    public function restore($id)
    {
        $program = InternshipProgram::withTrashed()->findOrFail($id);
        $program->restore();
        return back()->with('flash.banner', 'Internship program restored.');
    }

    public function batchUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);
        // Parse CSV and create internship programs and assign to graduates
        // (Implement CSV parsing logic here)
        return back()->with('flash.banner', 'Batch upload complete.');
    }

    public function assignToGraduate(Request $request)
    {
        $request->validate([
            'graduate_ids' => 'required|array|min:1',
            'graduate_ids.*' => 'exists:graduates,id',
            'internship_program_id' => 'required|exists:internship_programs,id',
        ]);
        foreach ($request->graduate_ids as $graduateId) {
            $graduate = Graduate::findOrFail($graduateId);
            $graduate->internshipPrograms()->syncWithoutDetaching([$request->internship_program_id]);
        }
        return back()->with('flash.banner', 'Internship program assigned to selected graduates.');
    }

    public function archivedList()
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->firstOrFail();
        $archived = InternshipProgram::with(['programs', 'careerOpportunities', 'skills'])
            ->onlyTrashed()
            ->where('institution_id', $institution->id)
            ->get();

        return Inertia::render('Institutions/InternshipPrograms/ArchivedList', [
            'internshipPrograms' => $archived,
        ]);
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->firstOrFail();

        $programFilter = $request->input('program_id');
        $careerFilter = $request->input('career_opportunity_id');

        // Get institution's available programs and career opportunities
        $programs = $institution->institutionPrograms()->with('program')->get()->map(fn($ip) => $ip->program)->unique('id')->values();
        $careerOpportunities = $institution->institutionCareerOpportunities()->with('careerOpportunity')->get()->map(fn($ico) => $ico->careerOpportunity)->unique('id')->values();

        // Query internship programs with relationships
        $query = InternshipProgram::with(['programs', 'careerOpportunities', 'skills'])
            ->where('institution_id', $institution->id);

        if ($programFilter) {
            $query->whereHas('programs', function ($q) use ($programFilter) {
                $q->where('program_id', $programFilter);
            });
        }
        if ($careerFilter) {
            $query->whereHas('careerOpportunities', function ($q) use ($careerFilter) {
                $q->where('career_opportunity_id', $careerFilter);
            });
        }

        $internshipPrograms = $query->get();

        return Inertia::render('Institutions/InternshipPrograms/List', [
            'internshipPrograms' => $internshipPrograms,
            'programs' => $programs,
            'careerOpportunities' => $careerOpportunities,
            'selectedProgram' => $programFilter,
            'selectedCareerOpportunity' => $careerFilter,
        ]);
    }

    public function assignPage()
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->firstOrFail();

        $internshipPrograms = InternshipProgram::with(['programs', 'careerOpportunities', 'graduates'])
            ->where('institution_id', $institution->id)
            ->get();

        $graduates = Graduate::where('institution_id', $institution->id)->get();

        // Get institution's programs
        $programs = $institution->institutionPrograms()
            ->with('program.degree')
            ->get()
            ->map(function ($ip) {
                return [
                    'id' => $ip->program->id,
                    'name' => $ip->program->name,
                    'degree' => $ip->degree ? $ip->degree->type : null,
                ];
            });

        // Get institution's career opportunities (with program_id)
        $careerOpportunities = $institution->institutionCareerOpportunities()
            ->with(['careerOpportunity', 'program'])
            ->get()
            ->map(function ($ico) {
                return [
                    'id' => $ico->careerOpportunity->id,
                    'title' => $ico->careerOpportunity->title,
                    'program_id' => $ico->program_id,
                ];
            });

        return Inertia::render('Institutions/InternshipPrograms/AssignInternship', [
            'internshipPrograms' => $internshipPrograms,
            'graduates' => $graduates,
            'programs' => $programs,
            'institutionCareerOpportunities' => $careerOpportunities,
        ]);
    }

    public function removeGraduate(Request $request)
    {
        $request->validate([
            'internship_program_id' => 'required|exists:internship_programs,id',
            'graduate_id' => 'required|exists:graduates,id',
        ]);
        $internship = InternshipProgram::findOrFail($request->internship_program_id);
        $internship->graduates()->detach($request->graduate_id);
        return back()->with('flash.banner', 'Graduate removed from internship.');
    }
}