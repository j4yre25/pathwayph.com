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
            'graduate_education_institution_id' => 'required|string|max:255',
            'graduate_education_program' => 'required|string|max:255',
            'graduate_education_field_of_study' => 'required|string|max:255',
            'graduate_education_start_date' => 'required|date',
            'graduate_education_end_date' => 'nullable|date',
            'graduate_education_description' => 'nullable|string',
        ]);

        $education = new Education($request->all());
        $education->user_id = auth()->id(); // Assuming you have a user_id field
        $education->save();

        return response()->json(['message' => 'Education added successfully.']);
    }

    // Update education
    public function updateEducation(Request $request, $id)
    {
        $request->validate([
            'graduate_education_institution_id' => 'required|string|max:255',
            'graduate_education_program' => 'required|string|max:255',
            'graduate_education_field_of_study' => 'required|string|max:255',
            'graduate_education_start_date' => 'required|date',
            'graduate_education_end_date' => 'nullable|date',
            'graduate_education_description' => 'nullable|string',
        ]);

        $education = Education::findOrFail($id);
        $education->update($request->all());

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