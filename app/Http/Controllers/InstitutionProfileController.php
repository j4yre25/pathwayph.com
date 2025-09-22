<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

        $user->is_approved = null;
        $user->has_completed_information = true;
        $user->save();



        session(['information_completed' => true]);
        return back()->with('information_saved', true);
    }

    public function updateDescription(Request $request)
    {
        $user = Auth::user();
        $institution = $user->institution;

        $validated = $request->validate([
            'institution_description' => 'nullable|string|max:5000',
        ]);

        if ($institution) {
            $institution->institution_description = $validated['institution_description'];
            $institution->save();
        }

        return back()->with('success', 'Institution description updated successfully!');
    }

    public function settings()
    {
        $user = Auth::user();
        $institution = $user->institution;
        
        // Get institution social links if they exist
        $socialLinks = $institution->social_links ?? [];
        
        return Inertia::render('Institutions/Profile/InstitutionProfileSettings', [
            'institution' => [
                'institution_name' => $institution->institution_name ?? 'N/A',
                'institution_email' => $institution->institution_email ?? 'N/A',
                'mobile_number' => $institution->institution_mobile_phone ?? 'N/A',
                'telephone_number' => $institution->institution_tel_phone ?? 'N/A',
                'address' => implode(', ', array_filter([
                    $institution->institution_street_address,
                    $institution->institution_brgy,
                    $institution->institution_city,
                    $institution->institution_province,
                    $institution->institution_zip_code
                ])),
                'description' => $institution->institution_description ?? null,
                'social_links' => $socialLinks,
                'profile_photo_path' => $user->profile_photo_path ? Storage::url($user->profile_photo_path) : null,
            ],
        ]);
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $institution = $user->institution;
        
        // Validate the request
        $validated = $request->validate([
            'institution_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:20',
            'telephone_number' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:5000',
            'website' => 'nullable|string|max:255',
            'indeed_profile' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
        ]);
        
        // Update institution information
        if ($institution) {
            $institution->institution_name = $validated['institution_name'];
            $institution->email = $validated['email'];
            $institution->contact_number = $validated['contact_number'];
            $institution->telephone_number = $validated['telephone_number'] ?? $institution->telephone_number;
            $institution->description = $validated['description'] ?? $institution->description;
            
            // Update website and social links
            $institution->website = $validated['website'] ?? $institution->website;
            
            $institution->social_links = [
                'indeed_profile' => $validated['indeed_profile'] ?? null,
                'facebook' => $validated['facebook'] ?? null,
                'twitter' => $validated['twitter'] ?? null,
                'linkedin' => $validated['linkedin'] ?? null,
                'instagram' => $validated['instagram'] ?? null,
            ];
            
            $institution->save();
        }
        
        // Handle logo upload if present
        if ($request->hasFile('logo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = $request->file('logo')->store('profile-photos', 'public');
            $user->save();
        }
        
        return back()->with('success', 'Institution profile settings updated successfully!');
    }
}
