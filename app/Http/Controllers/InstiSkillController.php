<?php

namespace App\Http\Controllers;

use App\Models\InstiSkill;
use App\Models\Program;
use App\Models\CareerOpportunity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstiSkillController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('status', 'active');

        $query = InstiSkill::with(['programs', 'careerOpportunities']);

        if ($filter === 'inactive') {
            $query->onlyTrashed();
        }

        $skills = $query->get()->map(function ($skill) {
            return [
                'id' => $skill->id,
                'name' => $skill->name,
                'programs' => $skill->programs->pluck('name'),
                'program_ids' => $skill->programs->pluck('id'),
                'career_opportunities' => $skill->careerOpportunities->pluck('title'),
                'career_opportunity_ids' => $skill->careerOpportunities->pluck('id'),
            ];
        });

        return Inertia::render('Institutions/InstiSkills', [
            'skills' => $skills,
            'programs' => Program::all(),
            'careerOpportunities' => CareerOpportunity::all(),
            'filter' => $filter,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:skills,name',
            'program_ids' => 'required|array|min:1',
            'program_ids.*' => 'exists:programs,id',
            'career_opportunity_ids' => 'required|array|min:1',
            'career_opportunity_ids.*' => 'exists:career_opportunities,id',
        ]);

        $skill = InstiSkill::create(['name' => $validated['name']]);
        $skill->programs()->attach($validated['program_ids']);
        $skill->careerOpportunities()->attach($validated['career_opportunity_ids']);

        return redirect()->route('skills.index')->with('success', 'Skill added successfully.');
    }

    public function update(Request $request, InstiSkill $skill)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:skills,name,' . $skill->id,
            'program_ids' => 'required|array|min:1',
            'program_ids.*' => 'exists:programs,id',
            'career_opportunity_ids' => 'required|array|min:1',
            'career_opportunity_ids.*' => 'exists:career_opportunities,id',
        ]);

        $skill->update(['name' => $validated['name']]);
        $skill->programs()->sync($validated['program_ids']);
        $skill->careerOpportunities()->sync($validated['career_opportunity_ids']);

        return redirect()->route('skills.index')->with('success', 'Skill updated successfully.');
    }

    public function destroy(InstiSkill $skill)
    {
        $skill->delete();
        return redirect()->route('skills.index')->with('success', 'Skill archived.');
    }

    public function restore($id)
    {
        $skill = InstiSkill::withTrashed()->findOrFail($id);
        $skill->restore();

        return redirect()->route('skills.index')->with('success', 'Skill restored.');
    }
}
