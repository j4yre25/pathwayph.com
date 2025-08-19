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
                    $user->company->company_street_address,
                    $user->company->company_brgy,
                    $user->company->company_city,
                    $user->company->company_province,
                    $user->company->company_zip_code
                ])),
                'sector' => $user->sector->name ?? null,
                'description' => $user->company_description ?? null,
                'mobile_phone' => $user->company->company_mobile_phone ?? null,
                'telephone_number' => $user->company->company_tel_phone ?? null,
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

   public function edit()
    {
        $user = Auth::user()->load('company');
        return Inertia::render('Profile/Show', [
            'user' => $user,
            'company' => $user->company,
        ]);
    }
    public function update(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_street_address' => ['required', 'string', 'max:255'],
            'company_brgy' => ['required', 'string', 'max:255'],
            'company_city' => ['required', 'string', 'max:255'],
            'company_province' => ['required', 'string', 'max:255'],
            'company_zip_code' => ['required', 'string', 'max:4'],
            'company_contact_number' => ['required', 'string', 'max:15'],
            'company_email' => ['required', 'string', 'email', 'max:255'],
            'telephone_number' => ['nullable', 'string', 'max:20'],
            'company_description' => ['nullable', 'string', 'max:1000'],
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
            'cover_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Update company fields
        if ($company) {
            $company->company_name = $validated['company_name'];
            $company->company_street_address = $validated['company_street_address'];
            $company->company_brgy = $validated['company_brgy'];
            $company->company_city = $validated['company_city'];
            $company->company_province = $validated['company_province'];
            $company->company_zip_code = $validated['company_zip_code'];
            $company->company_email = $validated['company_email'];
            $company->company_tel_phone = $validated['telephone_number'] ?? $company->company_tel_phone;
            $company->company_description = $validated['company_description'] ?? $company->company_description;
            $company->save();
        }

        // Update user fields
        $user->company_contact_number = $validated['company_contact_number'];
        $user->save();

        // Handle profile photo upload if present
        if ($request->hasFile('photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = $request->file('photo')->store('profile-photos', 'public');
            $user->save();
        }

        // Handle cover photo upload if present
        if ($request->hasFile('cover_photo')) {
            if ($user->cover_photo_path) {
                Storage::disk('public')->delete($user->cover_photo_path);
            }
            $user->cover_photo_path = $request->file('cover_photo')->store('cover-photos', 'public');
            $user->save();
        }

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
        $user = auth()->user();

        // Get all sectors and categories
        $sectors = \App\Models\Sector::all(['id', 'name']);
        $categories = \App\Models\Category::all(['id', 'name', 'sector_id']);

        return Inertia::render('Company/InformationSection', [
            'email' => $user->email,
            'sectors' => $sectors,
            'categories' => $categories,
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
            'category_id' => 'required|exists:categories,id',
            // HR
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'gender' => 'required|in:Male,Female',
            'dob' => 'required|date',
            'email' => 'required|email|max:255',
            'mobile_number' => 'required|string|max:20',
            'verification_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $verificationPath = null;
        if ($request->hasFile('verification_file')) {
            $verificationPath = $request->file('verification_file')->store('verification-documents', 'public');
        }

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
            'category_id' => $validated['category_id'],
            'sector_id' => \App\Models\Category::find($validated['category_id'])->sector_id,
            'verification_file_path' => $verificationPath,
        ]);

        // Create HR (human_resources)
         \App\Models\HumanResource::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'],
            'gender' => $validated['gender'],
            'dob' => $validated['dob'],
            'email' => $validated['email'],
            'mobile_number' => $validated['mobile_number'],
            'is_main_hr' => true, // Mark as main HR
        ]);

        $paddedCompanyId = str_pad($company->id, 3, '0', STR_PAD_LEFT);
        $sectorCode = $company->sector->sector_id ?? '000';
        $divisionCode = $company->category->division_code ?? '00';
        $company->company_id = "C-{$paddedCompanyId}-{$sectorCode}{$divisionCode}";
        $company->save();

        $user->has_completed_information = true;
        $user->save();

        session(['information_completed' => true]);
        return back()->with('information_saved', true);
    }
}
