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
}
