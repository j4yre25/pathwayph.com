<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ResumeController extends Controller
{
    // Upload resume
    public function uploadResume(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $user = Auth::user();
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('resumes', 'public');
            $user->resume()->update(['file' => $path]); 
        }

        return response()->json(['message' => 'Resume uploaded successfully.']);
    }

    // Remove resume
    public function removeResume()
    {
        $user = Auth::user();
        $user->resume()->delete(); // Assuming you have a relationship set up

        return response()->json(['message' => 'Resume removed successfully.']);
    }
}