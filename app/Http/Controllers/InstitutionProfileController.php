<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InstitutionProfileController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        $institution = \App\Models\Institution::where('user_id', $user->id)->first();

        return Inertia::render('Institutions/Profile/InstitutionProfile', [
            'institution' => [
                'id' => $institution->id,
                'name' => $institution->institution_name,
                'address' => $institution->institution_address ?? $institution->address,
                'contact_number' => $institution->contact_number,
                'telephone_number' => $institution->telephone_number,
                'email' => $institution->email,
                'logo' => $institution->logo ?? null,
                'cover_photo' => $institution->cover_photo ?? null,
                'description' => $institution->description ?? null,
                'social_links' => $institution->social_links ?? [],
                'is_featured' => $institution->is_featured ?? false,
                'created_at' => $institution->created_at?->format('F j, Y') ?? null,
                'institution_president_first_name' => $institution->institution_president_first_name ?? '',
                'institution_president_last_name' => $institution->institution_president_last_name ?? '',
            ],
        ]);
    }

    public function showInformationForm()
    {
        $user = auth()->user();

        return Inertia::render('Institutions/Profile/InformationSection', [
            'email' => $user->email,
            // Add more props if needed (e.g., for dropdowns)
        ]);
    }

    public function saveInformation(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'institution_name' => 'required|string|max:255',
            'institution_type' => 'required|string|max:255',
            'institution_address' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mobile_number' => 'required|string|max:20',
            'telephone_number' => 'nullable|string|max:20',
            'institution_career_officer_first_name' => 'required|string|max:255',
            'institution_career_officer_last_name' => 'required|string|max:255',
            'institution_president_first_name' => 'required|string|max:255',
            'institution_president_last_name' => 'required|string|max:255',
            'verification_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $verificationPath = null;
        if ($request->hasFile('verification_file')) {
            $verificationPath = $request->file('verification_file')->store('verification-documents', 'public');
        }

        // Create institution
        $institution = \App\Models\Institution::create([
            'user_id' => $user->id,
            'institution_name' => $validated['institution_name'],
            'institution_type' => $validated['institution_type'],
            'institution_address' => $validated['institution_address'],
            'email' => $validated['email'],
            'contact_number' => $validated['mobile_number'],
            'telephone_number' => $validated['telephone_number'],
            'institution_president_first_name' => $validated['institution_president_first_name'],
            'institution_president_last_name' => $validated['institution_president_last_name'],
            'verification_file_path' => $verificationPath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $institution->save();

        return back()->with('information_saved', true);
    }

    public function updateDescription(Request $request)
    {
        $user = Auth::user();
        $institution = \App\Models\Institution::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'description' => 'nullable|string|max:2000',
        ]);

        $institution->description = $validated['description'];
        $institution->save();

        return back()->with('success', 'Description updated!');
    }
}
