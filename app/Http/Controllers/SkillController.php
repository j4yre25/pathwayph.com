<?php

namespace App\Http\Controllers;

use App\Models\CareerOpportunity;
use App\Models\InstitutionSkill;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SkillController extends Controller
{
    public function index(User $user, Request $request)
    {
        $filter = $request->input('career_opportunity_id');

        // Ensure careerOpportunities is unique by title
        $careerOpportunities = $user->careerOpportunities()
            ->whereNull('institution_career_opportunities.deleted_at')
            ->get()
            ->unique('title')
            ->values(); // Ensure proper indexing

        $query = InstitutionSkill::with(['skill', 'careerOpportunity'])
            ->where('institution_id', $user->id);

        if ($filter) {
            $query->where('career_opportunity_id', $filter);
        }

        $skills = $query->get();

        return Inertia::render('Institutions/Skills/Index', [
            'careerOpportunities' => $careerOpportunities ?? [],
            'skills' => $skills,
            'selectedCareerOpportunity' => $filter,
        ]);
    }

    public function create(User $user)
    {
        $careerOpportunities = $user->careerOpportunities()
            ->whereNull('institution_career_opportunities.deleted_at')
            ->get();

        return Inertia::render('Institutions/Skills/Create', [
            'careerOpportunities' => $careerOpportunities,
        ]);
    }

    public function store(Request $request, User $user)
    {
        $request->validate([
            'career_opportunity_ids' => 'required|array|min:1',
            'career_opportunity_ids.*' => 'exists:career_opportunities,id',
            'skill_names' => 'required|string',
            'program_id' => 'nullable|exists:programs,id',
        ]);

        $skillNames = array_filter(array_map(fn ($name) => strtolower(trim($name)), explode(',', $request->skill_names)));
        $careerOpportunityIds = $request->career_opportunity_ids;
        $programId = $request->program_id;

        DB::beginTransaction();

        try {
            foreach ($skillNames as $rawName) {
                $formattedName = Str::title($rawName);

                $skill = Skill::withTrashed()->whereRaw('LOWER(name) = ?', [$rawName])->first();

                if (!$skill) {
                    $skill = Skill::create(['name' => $formattedName]);
                }

                foreach ($careerOpportunityIds as $careerId) {
                    $exists = InstitutionSkill::where([
                        'skill_id' => $skill->id,
                        'career_opportunity_id' => $careerId,
                        'institution_id' => $user->id,
                        'program_id' => $programId,
                    ])->exists();

                    if (!$exists) {
                        InstitutionSkill::create([
                            'skill_id' => $skill->id,
                            'career_opportunity_id' => $careerId,
                            'institution_id' => $user->id,
                            'program_id' => $programId,
                        ]);
                    }
                }
            }

            DB::commit();
            return back()->with('flash.banner', 'Skills added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['flash.banner' => 'Failed to save skills.']);
        }
    }

    public function edit($id)
    {
        $entry = InstitutionSkill::with(['skill', 'careerOpportunity'])->findOrFail($id);
        $institution = auth()->user();

        // Ensure careerOpportunities is an array and unique by title
        $careerOpportunities = $institution->careerOpportunities()
            ->whereNull('institution_career_opportunities.deleted_at')
            ->get()
            ->unique('title')
            ->values(); // Ensure proper indexing

        return Inertia::render('Institutions/Skills/Edit', [
            'link' => $entry,
            'careerOpportunities' => $careerOpportunities ?? [],
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'skill_name' => 'required|string',
            'career_opportunity_id' => 'required|exists:career_opportunities,id',
            'program_id' => 'nullable|exists:programs,id',
        ]);

        $entry = InstitutionSkill::findOrFail($id);

        $rawName = strtolower(trim($request->skill_name));
        $formattedName = Str::title($rawName);

        $skill = Skill::whereRaw('LOWER(name) = ?', [$rawName])->first();

        if (!$skill) {
            $skill = Skill::create(['name' => $formattedName]);
        }

        $entry->update([
            'skill_id' => $skill->id,
            'career_opportunity_id' => $request->career_opportunity_id,
            'program_id' => $request->program_id,
        ]);

        return back()->with('flash.banner', 'Skill updated successfully.');
    }

    public function archive($id)
    {
        $entry = InstitutionSkill::findOrFail($id);
        $entry->delete();

        return back()->with('flash.banner', 'Skill archived.');
    }

    public function restore($id)
    {
        $entry = InstitutionSkill::withTrashed()->findOrFail($id);
        $entry->restore();

        return back()->with('flash.banner', 'Skill restored.');
    }

    public function archivedList(User $user)
    {
        $archivedSkills = InstitutionSkill::with(['skill', 'careerOpportunity']) // Ensure relationships are eager-loaded
            ->onlyTrashed()
            ->where('institution_id', $user->id)
            ->get();

        return Inertia::render('Institutions/Skills/ArchivedList', [
            'skills' => $archivedSkills,
        ]);
    }

    public function list(Request $request, User $user)
    {
        $filter = $request->input('career_opportunity_id');

        // Ensure careerOpportunities is unique by title
        $careerOpportunities = $user->careerOpportunities()
            ->whereNull('institution_career_opportunities.deleted_at')
            ->get()
            ->unique('title')
            ->values(); // Ensure proper indexing

        $query = InstitutionSkill::with(['skill', 'careerOpportunity'])
            ->where('institution_id', $user->id);

        if ($filter) {
            $query->where('career_opportunity_id', $filter);
        }

        $skills = $query->get();

        return Inertia::render('Institutions/Skills/List', [
            'careerOpportunities' => $careerOpportunities ?? [],
            'skills' => $skills,
            'selectedCareerOpportunity' => $filter,
        ]);
    }
}
