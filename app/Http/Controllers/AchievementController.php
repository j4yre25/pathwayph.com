<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Achievement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AchievementController extends Controller
{
    // Add achievement
    public function addAchievement(Request $request)
    {
        $request->validate([
            'graduate_achievement_title' => 'required|string|max:255',
            'graduate_achievement_issuer' => 'required|string|max:255',
            'graduate_achievement_date' => 'required|date',
            'graduate_achievement_description' => 'nullable|string',
            'graduate_achievement_url' => 'nullable|string|max:255',
            'graduate_achievement_type' => 'nullable|string|max:255',
        ]);

        $achievement = new Achievement($request->all());
        $achievement->user_id = Auth::user(); // Assuming you have a user_id field
        $achievement->save();

        return response()->json(['message' => 'Achievement added successfully.']);
    }

    // Update achievement
    public function updateAchievement(Request $request, $id)
    {
        $request->validate([
            'graduate_achievement_title' => 'required|string|max:255',
            'graduate_achievement_issuer' => 'required|string|max:255',
            'graduate_achievement_date' => 'required|date',
            'graduate_achievement_description' => 'nullable|string',
            'graduate_achievement_url' => 'nullable|string|max:255',
            'graduate_achievement_type' => 'nullable|string|max:255',
        ]);

        $achievement = Achievement::findOrFail($id);
        $achievement->update($request->all());

        return response()->json(['message' => 'Achievement updated successfully.']);
    }

    // Remove achievement
    public function removeAchievement($id)
    {
        $achievement = Achievement::findOrFail($id);
        $achievement->delete();

        return response()->json(['message' => 'Achievement removed successfully.']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'graduate_achievement_title' => 'required|string',
            'graduate_achievement_issuer' => 'required|string',
            'graduate_achievement_date' => 'required|date',
            'graduate_achievement_description' => 'nullable|string',
            'graduate_achievement_url' => 'nullable|string',
            'graduate_achievement_type' => 'required|string',
            'credential_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Format the date using Carbon
        $validated['graduate_achievement_date'] = Carbon::parse($request->graduate_achievement_date)->format('Y-m-d');

        $achievement = new Achievement($request->except('credential_picture'));
        $achievement->user_id = Auth::user();

        if ($request->hasFile('credential_picture')) {
            $path = $request->file('credential_picture')->store('achievements', 'public');
            $achievement->credential_picture = $path;
        }

        $achievement->save();

        return response()->json([
            'id' => $achievement->id,
            'credential_picture_url' => $achievement->credential_picture ? 
                Storage::url($achievement->credential_picture) : null
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'graduate_achievement_title' => 'required|string',
            'graduate_achievement_issuer' => 'required|string',
            'graduate_achievement_date' => 'required|date',
            'graduate_achievement_description' => 'nullable|string',
            'graduate_achievement_url' => 'nullable|string',
            'graduate_achievement_type' => 'required|string',
            'credential_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Format the date using Carbon
        $validated['graduate_achievement_date'] = Carbon::parse($request->graduate_achievement_date)->format('Y-m-d');

        $achievement = new Achievement($request->except('credential_picture'));
        $achievement->user_id = Auth::id();

        if ($request->hasFile('credential_picture')) {
            $path = $request->file('credential_picture')->store('achievements', 'public');
            $achievement->credential_picture = $path;
        }

        $achievement->save();

        return response()->json([
            'id' => $achievement->id,
            'credential_picture_url' => $achievement->credential_picture ? 
                Storage::url($achievement->credential_picture) : null
        ]);
    }
}