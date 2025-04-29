<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Program;
use App\Models\CareerOpportunity;
use App\Models\SchoolYear;
use App\Models\Degree;
use App\Models\InstiSkill; // if you are keeping InstiSkill model name

use Illuminate\Http\Request;
use Inertia\Inertia;

class InstitutionController extends Controller
{
    /**
     * Main Institution Management Page (Tabbed)
     */
    public function index()
    {
        return Inertia::render('Institutions/Index', [
            'schoolYears' => SchoolYear::withTrashed()->orderBy('school_year_range')->get(),
            'degrees' => Degree::withTrashed()->orderBy('name')->get(),
            'programs' => Program::withTrashed()->with('degree')->orderBy('name')->get(),
            'careerOpportunities' => CareerOpportunity::withTrashed()->with('programs')->orderBy('title')->get(),
            'skills' => InstiSkill::withTrashed()->with(['programs', 'careerOpportunities'])->orderBy('name')->get(),
        ]);
    }

    /**
     * Legacy function - can be removed if not used
     */
    public function create()
    {
        return Inertia::render('Institutions/Create', [
            'programs' => Program::all(),
            'schoolYears' => SchoolYear::all(),
        ]);
    }

    /**
     * Legacy data creation – optional use for SchoolYear + Program combo
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_year_range' => 'required|string|regex:/^\d{4}-\d{4}$/',
            'term' => 'required|string',
            'program_id' => 'required|exists:programs,id',
        ]);

        Institution::create($validated);

        return redirect()->route('institutions.index')->with('success', 'Institution entry added successfully.');
    }

    /**
     * Show individual institution entry (optional)
     */
    public function show(Institution $institution)
    {
        return Inertia::render('Institutions/Show', [
            'institution' => $institution,
        ]);
    }

    /**
     * Edit form rendering (optional, if needed)
     */
    public function edit(Institution $institution)
    {
        return Inertia::render('Institutions/Edit', [
            'institution' => $institution,
            'programs' => Program::all(),
            'schoolYears' => SchoolYear::all(),
        ]);
    }

    /**
     * Update entry
     */
    public function update(Request $request, Institution $institution)
    {
        $validated = $request->validate([
            'school_year_range' => 'required|string|regex:/^\d{4}-\d{4}$/',
            'term' => 'required|string',
            'program_id' => 'required|exists:programs,id',
        ]);

        $institution->update($validated);

        return redirect()->route('institutions.index')->with('success', 'Institution entry updated successfully.');
    }

    /**
     * Soft delete (archive)
     */
    public function destroy(Institution $institution)
    {
        $institution->delete();

        return redirect()->route('institutions.index')->with('success', 'Institution entry archived.');
    }
}
