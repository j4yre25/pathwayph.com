<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Education;

class EducationController extends Controller
{
    // Add education
    public function addEducation(Request $request)
    {
        $request->validate([
            'education' => 'required|string|max:255',
            'program' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'achievements' => 'nullable|string',
            'is_current' => 'nullable|boolean',
        ]);

        $education = new Education();
        $education->education = $request->input('education');
        $education->program = $request->input('program');
        $education->field_of_study = $request->input('field_of_study');
        $education->start_date = $request->input('start_date');
        $education->end_date = $request->input('end_date');
        $education->description = $request->input('description');
        $education->achievements = $request->input('achievements');
        $education->is_current = $request->input('is_current', false);
        $education->user_id = auth()->id(); // Or graduate_id if you use that
        $education->save();

        return response()->json(['message' => 'Education added successfully.']);
    }

    // Update education
    public function updateEducation(Request $request, $id)
    {
        $request->validate([
            'education' => 'required|string|max:255',
            'program' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'achievements' => 'nullable|string',
            'is_current' => 'nullable|boolean',
        ]);

        $education = Education::findOrFail($id);
        $education->education = $request->input('education');
        $education->program = $request->input('program');
        $education->field_of_study = $request->input('field_of_study');
        $education->start_date = $request->input('start_date');
        $education->end_date = $request->input('end_date');
        $education->description = $request->input('description');
        $education->achievements = $request->input('achievements');
        $education->is_current = $request->input('is_current', false);
        $education->save();

        return response()->json(['message' => 'Education updated successfully.']);
    }

    // Remove education
    public function removeEducation($id)
    {
        $education = Education::findOrFail($id);
        $education->delete();

        return response()->json(['message' => 'Education removed successfully.']);
    }
}