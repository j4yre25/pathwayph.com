<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PesoProfileController extends Controller
{
    public function profile()
    {
        $user = Auth::user();

        return Inertia::render('Admin/PesoProfile', [
            'peso' => [
                'peso_first_name' => $user->peso_first_name,
                'peso_last_name' => $user->peso_last_name,
                'email' => $user->email,
                'contact_number' => $user->company_contact_number,
                'description' => $user->description,
            ],
        ]);
    }

    public function settings()
    {
        $user = Auth::user();

        return Inertia::render('Admin/PesoProfileSettings', [
            'peso' => [
                'peso_first_name' => $user->peso_first_name,
                'peso_last_name' => $user->peso_last_name,
                'email' => $user->email,
                'contact_number' => $user->company_contact_number,
                'description' => $user->description,
                'logo' => $user->peso_logo,
                'address' => $user->peso_address,
                'social_links' => $user->peso_social_links ? json_decode($user->peso_social_links, true) : [],
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'peso_first_name' => 'required|string|max:255',
            'peso_last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'contact_number' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'social_links' => 'nullable|array',
            'social_links.facebook' => 'nullable|url',
            'social_links.twitter' => 'nullable|url',
            'social_links.linkedin' => 'nullable|url',
            'social_links.instagram' => 'nullable|url',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($user->peso_logo) {
                Storage::disk('public')->delete($user->peso_logo);
            }
            
            $logoPath = $request->file('logo')->store('peso-logos', 'public');
            $validated['peso_logo'] = $logoPath;
        }

        // Update user fields
        $user->update([
            'peso_first_name' => $validated['peso_first_name'],
            'peso_last_name' => $validated['peso_last_name'],
            'email' => $validated['email'],
            'company_contact_number' => $validated['contact_number'],
            'description' => $validated['description'],
            'peso_address' => $validated['address'] ?? null,
            'peso_logo' => $validated['peso_logo'] ?? $user->peso_logo,
            'peso_social_links' => isset($validated['social_links']) ? json_encode($validated['social_links']) : null,
        ]);

        return back()->with('flash.banner', 'Profile updated successfully!');
    }
}
