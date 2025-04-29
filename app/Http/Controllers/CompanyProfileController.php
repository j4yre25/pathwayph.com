<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;


class CompanyProfileController extends Controller
{
    public function profile()
    {
        $user = Auth::user();

        return Inertia::render('Company/CompanyProfile', [
            'company' => [
                'company_name' => $user->company_name,
                'company_email' => $user->company_email,
                'company_contact_number' => $user->company_contact_number,
                'address' => implode(', ', array_filter([
                    $user->company_street_address,
                    $user->company_brgy,
                    $user->company_city,
                    $user->company_province,
                    $user->company_zip_code
                ])),
                'sector' => $user->sector->name ?? null, // assuming relation exists
                'description' => $user->company_description,
                'telephone_number' => $user->telephone_number,
                'created_at' => $user->created_at->format('F j, Y'),
                'branch' => implode (', ', array_filter([
                    $user->company_brgy,
                    $user->company_city,
                ])),
                'profile_photo' => $user->profile_photo_path ? Storage::url($user->profile_photo_path) : null,
                'cover_photo' => $user->cover_photo_path ? Storage::url($user->cover_photo_path) : null,
            ],
        ]);
    }

    public function post(Request $request)
    {
        $user = Auth::user();

        // Validate the incoming request
        $request->validate([
            'company_description' => 'nullable|string|max:5000',
        ]);

        // Update the company description (assuming user is linked to a company)
        $company = $user->company;

        if ($company) {
            $company->company_description = $request->input('company_description');
            $company->save();


            return redirect()->back()->with('success', 'Description updated successfully.');

        }

        return redirect()->back()->with('error', 'Company not found.');

    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_street_address' => ['required', 'string', 'max:255'],
            'company_brgy' => ['required', 'string', 'max:255'],
            'company_city' => ['required', 'string', 'max:255'],
            'company_province' => ['required', 'string', 'max:255'],
            'company_zip_code' => ['required', 'string', 'max:4'],
            'company_contact_number' => ['required', 'numeric','digits_between:10,15', 'regex:/^9\d{9}$/'],
            'company_email' => ['required', 'string', 'email', 'max:255'],
            'company_telephone_number' =>  ['nullable', 'regex:/^(02\d{7}|0\d{2}\d{7})$/'],
            'company_description' => ['nullable', 'string', 'max:1000'],
            'profile_photo_path' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
            'cover_photo_path' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

        ]);

        $user->update($validated);

            // Handle profile photo upload if present
        if ($request->hasFile('profile_photo')) {
            // Delete old profile photo if it exists
            if ($user->profile_photo_path) {
                Storage::delete($user->profile_photo_path);
            }
            // Store new profile photo
            $user->profile_photo_path = $request->file('photo')->store('profile-photos', 'public');
        }

        // Handle cover photo upload if present
        if ($request->hasFile('cover_photo')) {
            // Delete old cover photo if it exists
            if ($user->cover_photo_path) {
                Storage::delete($user->cover_photo_path);
            }
            // Store new cover photo
            $user->cover_photo_path = $request->file('cover_photo')->store('cover-photos', 'public');
        }

        $user->save();

        return back()->with('success', 'Company profile updated!');
    }

    public function destroyPhoto(Request $request)
    {
        $request->user()->deleteProfilePhoto();
        return back()->with('success', 'Profile photo removed.');
    }

    public function destroyCoverPhoto(Request $request)
    {
        $request->user()->deleteCoverPhoto();
        return back()->with('success', 'Cover photo removed.');
    }
}
