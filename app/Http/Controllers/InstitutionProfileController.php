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

        // Build the institution profile data.
        // Change the field names if your schema differs.
        return Inertia::render('Institutions/Profile/InstitutionProfile', [
            'institution' => [
                'id'               => $user->id,
                'name'             => $user->institution_name, // e.g., Institution Name
                'email'            => $user->email,
                'contact_number'   => $user->company_contact_number,
                'description'      => $user->description,
                'address'          => $user->address, // Ensure this field exists
                'logo'             => $user->logo, // path to institution logo
                'cover_photo'      => $user->cover_photo, // path to cover photo
                'social_links'     => $user->social_links, // optional array of social links
                'is_featured'      => $user->is_featured, // boolean for featured status
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
            'website' => 'nullable|string|max:255',
            'mobile_number' => 'required|string|max:20',
            'institution_president_first_name' => 'required|string|max:255',
            'institution_president_last_name' => 'required|string|max:255',
            'telephone_number' => 'nullable|string|max:20',
        ]);

        $input = $validated;

        \App\Models\Institution::updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'institution_name' => $input['institution_name'],
                'institution_type' => $input['institution_type'],
                'institution_address' => $input['institution_address'],
                'address' => $input['institution_address'],
                'email' => $input['email'],
                'website' => $input['website'] ?? null,
                'contact_number' => $input['mobile_number'],
                'institution_president_first_name' => $input['institution_president_first_name'],
                'institution_president_last_name' => $input['institution_president_last_name'],
                'telephone_number' => $input['telephone_number'] ?? null,
            ]
        );

        return back()->with('information_saved', true);
    }
}
