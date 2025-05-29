<?php

namespace App\Http\Controllers;

use App\Models\InternshipProgram;
use App\Models\Graduate;
use App\Models\Program;
use App\Models\CareerOpportunity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InternshipProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = InternshipProgram::with(['program', 'careerOpportunity'])
            ->when($request->program_id, fn($q) => $q->where('program_id', $request->program_id))
            ->when($request->career_opportunity_id, fn($q) => $q->where('career_opportunity_id', $request->career_opportunity_id))
            ->when($request->skills, fn($q) => $q->where('skills', 'like', "%{$request->skills}%"))
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'active')
                    $q->where('is_active', true);
                if ($request->status === 'inactive')
                    $q->where('is_active', false);
            })
            ->withTrashed();

        $programs = Program::all();
        $careerOpportunities = CareerOpportunity::all();

        return Inertia::render('Institutions/InternshipPrograms/Index', [
            'internshipPrograms' => $query->get(),
            'programs' => $programs,
            'careerOpportunities' => $careerOpportunities,
        ]);
    }

    public function create()
    {
        $programs = Program::all();
        $careerOpportunities = CareerOpportunity::all();
        return Inertia::render('Institutions/InternshipPrograms/Create', [
            'programs' => $programs,
            'careerOpportunities' => $careerOpportunities,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'program_id' => 'required|exists:programs,id',
            'career_opportunity_id' => 'nullable|exists:career_opportunities,id',
            'skills' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
        InternshipProgram::create($request->all());
        return back()->with('flash.banner', 'Internship program added.');
    }

    public function update(Request $request, $id)
    {
        $program = InternshipProgram::withTrashed()->findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
            'program_id' => 'required|exists:programs,id',
            'career_opportunity_id' => 'nullable|exists:career_opportunities,id',
            'skills' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
        $program->update($request->all());
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
            'graduate_id' => 'required|exists:graduates,id',
            'internship_program_id' => 'required|exists:internship_programs,id',
        ]);
        $graduate = Graduate::findOrFail($request->graduate_id);
        $graduate->internshipPrograms()->syncWithoutDetaching([$request->internship_program_id]);
        return back()->with('flash.banner', 'Internship program assigned to graduate.');
    }
}