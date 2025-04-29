<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EmploymentPreferencesController extends Controller
{
    // Update employment preferences
    public function updateEmploymentPreferences(Request $request, User $user)
    {
        $request->validate([
            'jobTypes' => 'array',
            'salaryExpectations' => 'array',
            'preferredLocations' => 'array',
            'workEnvironment' => 'array',
            'availability' => 'array',
            'additionalNotes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $employmentPreferences = $user->employmentPreferences()->firstOrNew(); // Get existing or create new

        // Map request data to the model's attributes
        $employmentPreferences->job_types = json_encode($request->jobTypes);
        $employmentPreferences->salary_expectations = json_encode($request->salaryExpectations); // Store as JSON
        $employmentPreferences->preferred_locations = json_encode($request->preferredLocations);
        $employmentPreferences->work_environment = json_encode($request->workEnvironment);
        $employmentPreferences->availability = json_encode($request->availability);
        $employmentPreferences->additional_notes = $request->additionalNotes;

        $employmentPreferences->save(); // Save the model

        return response()->json(['message' => 'Employment preferences updated successfully.']);
    }
}