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
        $company = $user->companyThroughHR ?? $user->company ?? null;

        // Determine user role
        $userRole = $user->hasRole('company') ? 'company' : 'graduate';

        $jobPostCount = $company && method_exists($company, 'jobs') ? $company->jobs()->count() : 0;

        return Inertia::render('Company/CompanyProfile', [
            'userRole' => $userRole,
            'company' => [
                'job_post_count' => $jobPostCount,
                'company_name' => $company->company_name ?? 'N/A',
                'company_email' => $company->company_email ?? 'N/A',
                'company_contact_number' => $user->company_contact_number ?? 'N/A',
                'address' => implode(', ', array_filter([
                    $company?->company_street_address,
                    $company?->company_brgy,
                    $company?->company_city,
                    $company?->company_province,
                    $company?->company_zip_code,
                ])),
                'sector' => $company?->sector?->name ?? null,
                'category' => $company?->category?->name ?? null,
                'description' => $company?->company_description ?? null,
                'mobile_phone' => $company?->company_mobile_phone ?? null,
                'telephone_number' => $company?->company_tel_phone ?? null,
                'created_at' => $user->created_at?->format('F j, Y') ?? null,
                'job_post_count' => method_exists($company, 'jobs') ? ($company?->jobs()?->count() ?? 0) : ($user->jobs()->count()),
                // Use company-level photos
                'profile_photo' => $company?->company_profile_photo_path
                    ? Storage::url($company->company_profile_photo_path)
                    : ($company?->company_logo_path ? Storage::url($company->company_logo_path) : null),
                'cover_photo' => $company?->company_cover_photo_path ? Storage::url($company->company_cover_photo_path) : null,
            ],
        ]);
    }

    public function post(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'company_description' => 'nullable|string|max:5000',
        ]);

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
        return Inertia::render('Profile/Partials/UpdateCompanyProfileInformationForm', [
            'user' => $user,
            'company' => $user->company,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
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
            // Company media stored on companies table
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',       // company profile photo
            'cover_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:4096', // company cover photo
        ]);

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

            // Profile photo -> company_profile_photo_path
            if ($request->hasFile('photo')) {
                if ($company->company_profile_photo_path) {
                    Storage::disk('public')->delete($company->company_profile_photo_path);
                }
                $company->company_profile_photo_path = $request->file('photo')->store('company-profile-photos', 'public');
                $company->save();
            }

            // Cover photo -> company_cover_photo_path
            if ($request->hasFile('cover_photo')) {
                if ($company->company_cover_photo_path) {
                    Storage::disk('public')->delete($company->company_cover_photo_path);
                }
                $company->company_cover_photo_path = $request->file('cover_photo')->store('company-cover-photos', 'public');
                $company->save();
            }
        }

        // Keep contact number on user model if used elsewhere
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
        // if ($request->hasFile('cover_photo')) {
        //     if ($user->cover_photo_path) {
        //         Storage::disk('public')->delete($user->cover_photo_path);
        //     }
        //     $user->cover_photo_path = $request->file('cover_photo')->store('cover-photos', 'public');
        //     $user->save();
        // }

        return back()->with('success', 'Company profile updated!');
    }

    public function destroyPhoto(Request $request)
    {
        // If you intend to remove company profile photo instead of user photo, adjust accordingly.
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
        $company = \App\Models\Company::with(['jobs', 'sector', 'category'])->findOrFail($id);
        $jobPostCount = method_exists($company, 'jobs') ? $company->jobs()->count() : 0;

        return inertia('Company/CompanyProfile', [
            'company' => [
                ...$company->toArray(),
                'job_post_count' => $jobPostCount,
                'category' => $company->category?->name, // <-- Add this line for category name

            ],
        ]);
    }

    public function settings()
    {
        $user = Auth::user();
        $company = $user->company;

        // Get company social links if they exist
        $socialLinks = $company->social_links ?? [];

        return Inertia::render('Company/CompanyProfileSettings', [
            'company' => [
                'company_name' => $company->company_name ?? 'N/A',
                'company_email' => $company->company_email ?? 'N/A',
                'mobile_number' => $company->company_mobile_phone ?? 'N/A',
                'telephone_number' => $company->company_tel_phone ?? 'N/A',
                'address' => implode(', ', array_filter([
                    $company->company_street_address,
                    $company->company_brgy,
                    $company->company_city,
                    $company->company_province,
                    $company->company_zip_code
                ])),
                'sector' => $user->sector->name ?? null,
                'description' => $company->company_description ?? null,
                'social_links' => $socialLinks,
                'profile_photo_path' => $user->profile_photo_path ? Storage::url($user->profile_photo_path) : null,
            ],
        ]);
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Validate the request
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'mobile_number' => 'required|string|max:20',
            'telephone_number' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:5000',
            'indeed_profile' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
        ]);

        // Update company information
        if ($company) {
            $company->company_name = $validated['company_name'];
            $company->company_email = $validated['company_email'];
            $company->company_mobile_phone = $validated['mobile_number'];
            $company->company_tel_phone = $validated['telephone_number'] ?? $company->company_tel_phone;
            $company->company_description = $validated['description'] ?? $company->company_description;

            // Update social links
            $company->social_links = [
                'indeed' => $validated['indeed_profile'] ?? '',
                'facebook' => $validated['facebook'] ?? '',
                'twitter' => $validated['twitter'] ?? '',
                'linkedin' => $validated['linkedin'] ?? '',
                'instagram' => $validated['instagram'] ?? '',
                'website' => $validated['website'] ?? '',
            ];

            $company->save();
        }

        // Handle logo upload if present
        if ($request->hasFile('logo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = $request->file('logo')->store('profile-photos', 'public');
            $user->save();
        }

        return back()->with('success', 'Company profile settings updated successfully!');
    }

    public function showInformationForm()
    {
        $user = Auth::user();

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
        $user = Auth::user();

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
            // Documents
            'verification_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'bir_tin' => ['required', 'string', 'max:30'],
            'company_logo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $verificationPath = null;
        if ($request->hasFile('verification_file')) {
            $verificationPath = $request->file('verification_file')->store('verification-documents', 'public');
        }

        $logoPath = null;
        if ($request->hasFile('company_logo')) {
            $logoPath = $request->file('company_logo')->store('company-logos', 'public');
        }

        // Create company; set company_profile_photo_path to the same logo
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
            'bir_tin' => $validated['bir_tin'],
            'company_logo_path' => $logoPath,
            'company_profile_photo_path' => $logoPath,
        ]);

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
            'is_main_hr' => true,
        ]);

        $paddedCompanyId = str_pad($company->id, 3, '0', STR_PAD_LEFT);
        $sectorCode = $company->sector->sector_id ?? '000';
        $divisionCode = $company->category->division_code ?? '00';
        $company->company_id = "C-{$paddedCompanyId}-{$sectorCode}{$divisionCode}";
        $company->save();

        $user->is_approved = null;
        $user->has_completed_information = true;
        $user->save();

        session(['information_completed' => true]);
        return back()->with('information_saved', true);
    }
}
