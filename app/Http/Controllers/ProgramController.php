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
        $institution = Institution::where('user_id', $user->id)->first();

        $programs = InstitutionProgram::with('program.degree')
            ->where('institution_id', $institution?->id)
            ->get();

        // Pass only degrees for this institution
        $degrees = \App\Models\InstitutionDegree::with('degree')
            ->where('institution_id', $institution->id)
            ->whereNull('deleted_at')
            ->get()
            ->pluck('degree')
            ->map(fn($d) => ['id' => $d->id, 'type' => $d->type])
            ->values();

        return Inertia::render('Institutions/Programs/Index', [
            'programs' => $programs,
            'degrees' => $degrees,
        ]);
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->first();
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
        $institution = Institution::where('user_id', $user->id)->first();
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
        $institution = Institution::where('user_id', $user->id)->first();

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
        $institution = Institution::where('user_id', $user->id)->first();

        $degree = Degree::findOrFail($request->degree_id);
        $degreeInitials = $this->getDegreeInitials($degree->type);

        $request->validate([
            'name' => ['required', 'string'],
            'degree_id' => ['required', 'exists:degrees,id'],
            'program_code' => ['nullable', 'string', 'max:20'],
            'duration' => ['nullable', 'in:Month,Year'],
            'duration_time' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        // Check for duplicate program in this institution
        $existingProgram = Program::where('name', $request->name)
            ->where('degree_id', $request->degree_id)
            ->first();

        if ($existingProgram) {
            $duplicate = InstitutionProgram::where('institution_id', $institution->id)
                ->where('program_id', $existingProgram->id)
                ->exists();

            if ($duplicate) {
                return back()->with(['flash.banner' => 'This program already exists for your institution.']);
            }
        }

        if (in_array($degree->type, ['Doctoral', 'Diploma'])) {
            $programCodePrefix = $degreeInitials;
        } else {
            $programCodePrefix = $request->program_code;
        }

        // Find last code for this institution and program code
        $last = InstitutionProgram::where('institution_id', $institution->id)
            ->where('program_code', 'like', $programCodePrefix . '%')
            ->orderByDesc('id')
            ->first();

        if (in_array($degree->type, ['Diploma', 'Associate'])) {
            $duration = $request->duration;
            $durationTime = $request->duration_time;
            $durCode = $durationTime . strtoupper(substr($duration, 0, 1));
            // Find last code with this prefix and durCode
            $last = InstitutionProgram::where('institution_id', $institution->id)
                ->where('program_code', 'like', $programCodePrefix . '-' . $durCode . '%')
                ->orderByDesc('id')
                ->first();
            $number = 1;
            if ($last && preg_match('/-(?:\d+[MY])?(\d{3})$/', $last->program_code, $m)) {
                $number = intval($m[1]) + 1;
            }
            $finalCode = $programCodePrefix . '-' . $durCode . str_pad($number, 3, '0', STR_PAD_LEFT);
        } else {
            $number = $last && preg_match('/-(\d{3})$/', $last->program_code, $m) ? intval($m[1]) + 1 : 1;
            $finalCode = $programCodePrefix . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
        }

        // Create or get program
        $program = Program::firstOrCreate([
            'name' => $request->name,
            'degree_id' => $degree->id,
        ]);

        InstitutionProgram::create([
            'program_id' => $program->id,
            'degree_id' => $degree->id,
            'institution_id' => $institution->id,
            'program_code' => $finalCode,
            'duration' => $request->duration,
            'duration_time' => $request->duration_time,
        ]);

        return redirect()->back()->with('flash.banner', 'Program added.');
    }

    public function edit($id)
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->first();
        $institutionProgram = InstitutionProgram::with('program.degree')
            ->where('institution_id', $institution?->id)
            ->findOrFail($id);

        // Fetch only degrees linked to this institution
        $degrees = [];
        if ($institution) {
            $degrees = \App\Models\InstitutionDegree::with('degree')
                ->where('institution_id', $institution->id)
                ->whereNull('deleted_at')
                ->get()
                ->pluck('degree');
        }

        return Inertia::render('Institutions/Programs/Edit', [
            'institutionProgram' => $institutionProgram,
            'degrees' => $degrees,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->first();

        $request->validate([
            'name' => ['required', 'string'],
            'degree_id' => ['required', 'exists:degrees,id'],
            'program_code' => ['required', 'string', 'max:20'],
            'duration' => ['nullable', 'in:Month,Year'],
            'duration_time' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        // Check for duplicate program name in this institution (excluding current)
        $duplicateName = InstitutionProgram::where('institution_id', $institution->id)
            ->whereHas('program', function($q) use ($request) {
                $q->where('name', $request->name)
                  ->where('degree_id', $request->degree_id);
            })
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicateName) {
            return back()->with(['flash.banner' => 'This program name and degree already exists for your institution.']);
        }

        // Check for duplicate program code in this institution (excluding current)
        $duplicateCode = InstitutionProgram::where('institution_id', $institution->id)
            ->where('program_code', $request->program_code)
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicateCode) {
            return back()->with(['flash.banner' => 'This program code already exists for your institution.']);
        }

        // Find or create the program globally
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

        $institutionProgram = InstitutionProgram::where('institution_id', $institution->id)->findOrFail($id);

        $institutionProgram->update([
            'program_id' => $program->id,
            'degree_id' => $request->degree_id,
            'program_code' => $request->program_code,
            'duration' => $request->duration,
            'duration_time' => $request->duration_time,
        ]);

        return redirect()->back()->with('flash.banner', 'Program updated.');
    }

    public function delete($id)
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->first();
        $institutionProgram = InstitutionProgram::where('institution_id', $institution?->id)->findOrFail($id);
        $institutionProgram->delete();

        return back()->with('flash.banner', 'Program archived.');
    }

    public function restore($id)
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->first();
        $institutionProgram = InstitutionProgram::withTrashed()
            ->where('institution_id', $institution?->id)
            ->findOrFail($id);
        $institutionProgram->restore();

        return redirect()->back()->with('flash.banner', 'Program restored.');
    }

    private function getDegreeInitials($type)
{
    return [
        'Bachelor of Science' => 'BS',
        'Bachelor of Arts' => 'BA',
        'Associate' => 'AS',
        'Master of Science' => 'MS',
        'Master of Arts' => 'MA',
        'Doctoral' => 'PhD',
        'Diploma' => 'D',
    ][$type] ?? 'XX';
}
}
