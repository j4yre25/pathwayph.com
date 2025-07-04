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
        $company = $user->companyThroughHR ?? null;
         // Determine user role
        $userRole = $user->hasRole('company') ? 'company' : 'graduate';

        return Inertia::render('Company/CompanyProfile', [
            'userRole' => $userRole,
            'company' => [
                'company_name' => $company->company_name ?? 'N/A',
                'company_email' => $company->company_email ?? 'N/A',
                'company_contact_number' => $user->company_contact_number ?? 'N/A',
                'address' => implode(', ', array_filter([
                    $user->company_street_address,
                    $user->company_brgy,
                    $user->company_city,
                    $user->company_province,
                    $user->company_zip_code
                ])),
                'sector' => $user->sector->name ?? null,
                'description' => $user->company_description ?? null,
                'telephone_number' => $user->telephone_number ?? null,
                'created_at' => $user->created_at?->format('F j, Y') ?? null,
                'branch' => implode(', ', array_filter([
                    $user->company_brgy,
                    $user->company_city,
                ])),
                'job_post_count' => $user->jobs()->count(),
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

    public function showPublic($id)
    {
        $company = \App\Models\Company::with(['jobs', 'sector'])->findOrFail($id);
        return inertia('Frontend/CompanyProfile', [
            'company' => $company,
        ]);
    }

    public function showInformationForm()
    {
        $categories = \App\Models\Category::all();
        $user = auth()->user();
        return Inertia::render('Company/InformationSection', [
            'categories' => $categories,
             'user' => [
                'first_name' => $user->hr->first_name,
                'last_name' => $user->hr->last_name,
                'middle_name' => $user->hr->middle_name,
                'gender' => $user->hr->gender,
                'dob' => $user->hr->dob,
                'email' => $user->hr->email,
                'mobile_number' => $user->hr->mobile_number,
            ],
        ]);
    }

    public function saveInformation(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            // Company
            'company_name' => 'required|string|max:255',
            'company_street_address' => 'required|string|max:255',
            'company_brgy' => 'required|string|max:255',
            'company_city' => 'required|string|max:255',
            'company_province' => 'required|string|max:255',
            'company_zip_code' => 'required|string|max:10',
            'company_email' => 'required|email|max:255',
            'company_mobile_phone' => 'required|string|max:20',
            'telephone_number' => 'nullable|string|max:20',
            'category' => 'required|exists:categories,id',
            // HR
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'gender' => 'required|in:Male,Female',
            'dob' => 'required|date',
            'email' => 'required|email|max:255',
            'mobile_number' => 'required|string|max:20',
        ]);

        // Create company
        $company = \App\Models\Company::create([
            'user_id' => $user->id,
            'company_name' => $validated['company_name'],
            'company_street_address' => $validated['company_street_address'],
            'company_brgy' => $validated['company_brgy'],
            'company_city' => $validated['company_city'],
            'company_province' => $validated['company_province'],
            'company_zip_code' => $validated['company_zip_code'],
            'company_email' => $validated['company_email'],
            'company_mobile_phone' => $validated['company_mobile_phone'],
            'company_tel_phone' => $validated['telephone_number'],
            'category_id' => $validated['category'],
            'sector_id' => \App\Models\Category::find($validated['category'])->sector_id,
        ]);

        // Create HR (human_resources)
        $user->hr()->create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'],
            'gender' => $validated['gender'],
            'dob' => $validated['dob'],
            'email' => $validated['email'],
            'mobile_number' => $validated['mobile_number'],
            'company_id' => $company->id,
        ]);

        // Optionally update user profile if needed

        return redirect()->route('dashboard')->with('success', 'Company profile completed!');
    }
}
