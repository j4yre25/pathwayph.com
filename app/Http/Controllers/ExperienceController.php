<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Experience;

class ExperienceController extends Controller
{
    // Add experience
    public function addExperience(Request $request)
    {
        $request->validate([
            'graduate_experience_title' => 'required|string|max:255',
            'graduate_experience_company' => 'required|string|max:255',
            'graduate_experience_start_date' => 'required|date',
            'graduate_experience_end_date' => 'nullable|date',
            'graduate_experience_address' => 'nullable|string|max:255',
            'graduate_experience_achievements' => 'nullable|string',
            'graduate_experience_skills_tech' => 'nullable|string',
        ]);

        $experience = new Experience($request->all());
        // $experience->user_id = auth()->id(); // Assuming you have a user_id field
        $experience->save();

        return response()->json(['message' => 'Experience added successfully.']);
    }

    // Update experience
    public function updateExperience(Request $request, $id)
    {
        $request->validate([
            'graduate_experience_title' => 'required|string|max:255',
            'graduate_experience_company' => 'required|string|max:255',
            'graduate_experience_start_date' => 'required|date',
            'graduate_experience_end_date' => 'nullable|date',
            'graduate_experience_address' => 'nullable|string|max:255',
            'graduate_experience_achievements' => 'nullable|string',
            'graduate_experience_skills_tech' => 'nullable|string',
        ]);

        $experience = Experience::findOrFail($id);
        $experience->update($request->all());

        return response()->json(['message' => 'Experience updated successfully.']);
    }

    // Remove experience
    public function removeExperience($id)
    {
        $experience = Experience::findOrFail($id);
        $experience->delete();

        return response()->json(['message' => 'Experience removed successfully.']);
    }
}