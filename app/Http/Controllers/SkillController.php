<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Skill;

class SkillController extends Controller
{
    // Add skill
    public function addSkill(Request $request)
    {
        $request->validate([
            'graduate_skills_name' => 'required|string|max:255',
            'graduate_skills_proficiency' => 'required|integer|min:1|max:100',
            'graduate_skills_type' => 'required|string|max:255',
            'graduate_skills_years_experience' => 'required|integer|min:0',
        ]);

        $skill = new Skill($request->all());
        $skill->user_id = auth()->id(); // Assuming you have a user_id field
        $skill->save();

        return response()->json(['message' => 'Skill added successfully.']);
    }

    // Update skill
    public function updateSkill(Request $request, $id)
    {
        $request->validate([
            'graduate_skills_name' => 'required|string|max:255',
            'graduate_skills_proficiency' => 'required|integer|min:1|max:100',
            'graduate_skills_type' => 'required|string|max:255',
            'graduate_skills_years_experience' => 'required|integer|min:0',
        ]);

        $skill = Skill::findOrFail($id);
        $skill->update($request->all());

        return response()->json(['message' => 'Skill updated successfully.']);
    }

    // Remove skill
    public function removeSkill($id)
    {
        $skill = Skill::findOrFail($id);
        $skill->delete();

        return response()->json(['message' => 'Skill removed successfully.']);
    }
}