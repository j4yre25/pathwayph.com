<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Degree;
use App\Models\User;
use App\Models\Institution;
use App\Models\InstitutionProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProgramController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();
        $programs = InstitutionProgram::with('program.degree')
            ->where('institution_id', $institution?->id)
            ->get();

        return Inertia::render('Institutions/Programs/Index', [
            'programs' => $programs,
        ]);
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();
        $status = $request->input('status', 'all');

        $query = InstitutionProgram::with('program.degree')
            ->withTrashed()
            ->where('institution_id', $institution?->id);

        if ($status === 'active') {
            $query->whereNull('deleted_at');
        } elseif ($status === 'inactive') {
            $query->whereNotNull('deleted_at');
        }

        $programs = $query->get();

        return Inertia::render('Institutions/Programs/List', [
            'programs' => $programs,
            'status'   => $status,
        ]);
    }

    public function archivedList()
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();
        $all_programs = InstitutionProgram::with('program.degree')
            ->onlyTrashed()
            ->where('institution_id', $institution?->id)
            ->get();

        return Inertia::render('Institutions/Programs/ArchivedList', [
            'all_programs' => $all_programs,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();

        // Fetch only degrees linked to this institution
        $degrees = [];
        if ($institution) {
            $degrees = \App\Models\InstitutionDegree::with('degree')
                ->where('institution_id', $institution->id)
                ->whereNull('deleted_at')
                ->get()
                ->pluck('degree');
        }

        return Inertia::render('Institutions/Programs/Create', [
            'degrees' => $degrees,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();

        $request->validate([
            'name' => ['required', 'string'],
            'degree_id' => ['required', 'exists:degrees,id'],
        ]);

        // Check if program exists globally
        $program = Program::withTrashed()
            ->where('name', $request->name)
            ->where('degree_id', $request->degree_id)
            ->first();

        if (!$program) {
            $program = Program::create([
                'name' => $request->name,
                'degree_id' => $request->degree_id,
            ]);
        }

        // Check if this institution already has this program
        $exists = InstitutionProgram::withTrashed()
            ->where('institution_id', $institution->id)
            ->where('program_id', $program->id)
            ->exists();

        if ($exists) {
            return back()->with('flash.banner', 'This program already exists for your institution.');
        }

        InstitutionProgram::create([
            'program_id' => $program->id,
            'degree_id' => $request->degree_id,
            'institution_id' => $institution->id,
        ]);

        return redirect()->back()->with('flash.banner', 'Program added.');
    }

    public function edit($id)
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();
        $institutionProgram = InstitutionProgram::with('program.degree')
            ->where('institution_id', $institution?->id)
            ->findOrFail($id);

        $degrees = Degree::all();

        return Inertia::render('Institutions/Programs/Edit', [
            'institutionProgram' => $institutionProgram,
            'degrees' => $degrees,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();

        $request->validate([
            'name' => ['required', 'string'],
            'degree_id' => ['required', 'exists:degrees,id'],
        ]);

        // Check if program exists globally
        $program = Program::withTrashed()
            ->where('name', $request->name)
            ->where('degree_id', $request->degree_id)
            ->first();

        if (!$program) {
            $program = Program::create([
                'name' => $request->name,
                'degree_id' => $request->degree_id,
            ]);
        }

        // Check for duplicate in institution_programs
        $exists = InstitutionProgram::withTrashed()
            ->where('institution_id', $institution->id)
            ->where('program_id', $program->id)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->with('flash.banner', 'This program already exists for your institution.');
        }

        $institutionProgram = InstitutionProgram::where('institution_id', $institution->id)->findOrFail($id);

        $institutionProgram->update([
            'program_id' => $program->id,
            'degree_id' => $request->degree_id,
        ]);

        return redirect()->back()->with('flash.banner', 'Program updated.');
    }

    public function delete($id)
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();
        $institutionProgram = InstitutionProgram::where('institution_id', $institution?->id)->findOrFail($id);
        $institutionProgram->delete();

        return back()->with('flash.banner', 'Program archived.');
    }

    public function restore($id)
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();
        $institutionProgram = InstitutionProgram::withTrashed()
            ->where('institution_id', $institution?->id)
            ->findOrFail($id);
        $institutionProgram->restore();

        return redirect()->back()->with('flash.banner', 'Program restored.');
    }
}
