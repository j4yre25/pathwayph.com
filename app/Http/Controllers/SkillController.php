<?php

namespace App\Http\Controllers;

use App\Models\Institution;
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
        $institution = Institution::where('user_id', $user->id)->first();

        $careerOpportunities = [];
        if ($institution) {
            $careerOpportunities = $institution->institutionCareerOpportunities()
                ->with('careerOpportunity')
                ->whereNull('deleted_at')
                ->get()
                ->map(fn($ico) => $ico->careerOpportunity)
                ->unique('title')
                ->values();
        }

        $query = InstitutionSkill::with(['skill', 'careerOpportunity']);
        if ($institution) {
            $query->where('institution_id', $institution->id);
        } else {
            $query->whereRaw('0=1');
        }

        if ($filter) {
            $query->where('career_opportunity_id', $filter);
        }

        $skills = $query->get();

        return Inertia::render('Institutions/Skills/Index', [
            'careerOpportunities' => $careerOpportunities,
            'skills' => $skills,
            'selectedCareerOpportunity' => $filter,
        ]);
    }

    public function create(User $user)
    {
        $institution = Institution::where('user_id', $user->id)->first();

        $careerOpportunities = [];
        if ($institution) {
            $careerOpportunities = $institution->institutionCareerOpportunities()
                ->with('careerOpportunity')
                ->whereNull('deleted_at')
                ->get()
                ->map(fn($ico) => $ico->careerOpportunity)
                ->unique('title')
                ->values();
        }

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
        ]);

        $skillNames = array_filter(array_map(fn ($name) => strtolower(trim($name)), explode(',', $request->skill_names)));
        $careerOpportunityIds = $request->career_opportunity_ids;

        $institution = Institution::where('user_id', $user->id)->first();
        if (!$institution) {
            return back()->withErrors(['flash.banner' => 'Institution not found.']);
        }

        DB::beginTransaction();

        try {
            foreach ($skillNames as $rawName) {
                $formattedName = Str::title($rawName);

                // Check if the skill exists globally (case-insensitive)
                $skill = Skill::withTrashed()->whereRaw('LOWER(name) = ?', [$rawName])->first();

                if (!$skill) {
                    $skill = Skill::create(['name' => $formattedName]);
                }

                foreach ($careerOpportunityIds as $careerId) {
                    $exists = InstitutionSkill::where([
                        'skill_id' => $skill->id,
                        'career_opportunity_id' => $careerId,
                        'institution_id' => $institution->id,
                    ])->exists();

                    if (!$exists) {
                        InstitutionSkill::create([
                            'skill_id' => $skill->id,
                            'career_opportunity_id' => $careerId,
                            'institution_id' => $institution->id,
                        ]);
                    }
                }
            }

            DB::commit();
            return back()->with('flash.banner', 'Skills added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Skill store error: ' . $e->getMessage());
            return back()->withErrors(['flash.banner' => 'Failed to save skills.']);
        }
    }

    public function edit($id)
    {
        $entry = InstitutionSkill::with(['skill', 'careerOpportunity'])->findOrFail($id);
        $user = auth()->user();
        $institution = Institution::where('user_id', $user->id)->first();

        $careerOpportunities = [];
        if ($institution) {
            $careerOpportunities = $institution->institutionCareerOpportunities()
                ->with('careerOpportunity')
                ->whereNull('deleted_at')
                ->get()
                ->map(fn($ico) => $ico->careerOpportunity)
                ->unique('title')
                ->values();
        }

        return Inertia::render('Institutions/Skills/Edit', [
            'link' => $entry,
            'careerOpportunities' => $careerOpportunities,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'skill_name' => 'required|string',
            'career_opportunity_id' => 'required|exists:career_opportunities,id',
        ]);

        $entry = InstitutionSkill::findOrFail($id);

        $rawName = strtolower(trim($request->skill_name));
        $formattedName = Str::title($rawName);

        // Check if the skill exists globally (case-insensitive)
        $skill = Skill::withTrashed()->whereRaw('LOWER(name) = ?', [$rawName])->first();

        if (!$skill) {
            $skill = Skill::create(['name' => $formattedName]);
        }

        $entry->update([
            'skill_id' => $skill->id,
            'career_opportunity_id' => $request->career_opportunity_id,
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
        $institution = Institution::where('user_id', $user->id)->first();

        $archivedSkills = collect();
        if ($institution) {
            $archivedSkills = InstitutionSkill::with(['skill', 'careerOpportunity'])
                ->onlyTrashed()
                ->where('institution_id', $institution->id)
                ->get();
        }

        return Inertia::render('Institutions/Skills/ArchivedList', [
            'skills' => $archivedSkills,
        ]);
    }

    public function list(Request $request, User $user)
    {
        $filter = $request->input('career_opportunity_id');
        $institution = Institution::where('user_id', $user->id)->first();

        $careerOpportunities = [];
        if ($institution) {
            $careerOpportunities = $institution->institutionCareerOpportunities()
                ->with('careerOpportunity')
                ->whereNull('deleted_at')
                ->get()
                ->map(fn($ico) => $ico->careerOpportunity)
                ->unique('title')
                ->values();
        }

        $query = InstitutionSkill::with(['skill', 'careerOpportunity']);
        if ($institution) {
            $query->where('institution_id', $institution->id);
        } else {
            $query->whereRaw('0=1');
        }

        if ($filter) {
            $query->where('career_opportunity_id', $filter);
        }

        $skills = $query->get();

        return Inertia::render('Institutions/Skills/List', [
            'careerOpportunities' => $careerOpportunities,
            'skills' => $skills,
            'selectedCareerOpportunity' => $filter,
        ]);
    }
}
