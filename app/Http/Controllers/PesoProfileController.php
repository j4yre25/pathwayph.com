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
        $peso = \App\Models\Peso::where('user_id', $user->id)->first();

        return Inertia::render('Admin/PesoProfile', [
            'peso' => [
                'peso_first_name' => $peso?->peso_first_name,
                'peso_last_name' => $peso?->peso_last_name,
                'email' => $user->email,
                'contact_number' => $peso?->contact_number,
                'description' => $peso?->description,
                'address' => $peso?->address,
                'logo' => $peso?->logo ?? null,
                'social_links' => $peso?->social_links ? json_decode($peso->social_links, true) : [],
            ],
        ]);
    }

    public function settings()
    {
        $user = Auth::user();
        $peso = \App\Models\Peso::where('user_id', $user->id)->first();

        return Inertia::render('Admin/PesoProfileSettings', [
            'peso' => [
                'peso_first_name' => $peso?->peso_first_name,
                'peso_last_name' => $peso?->peso_last_name,
                'email' => $user->email,
                'contact_number' => $peso?->contact_number,
                'description' => $peso?->description,
                'address' => $peso?->address,
                'logo' => $peso?->logo ?? null,
                'social_links' => $peso?->social_links ? json_decode($peso->social_links, true) : [],
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $peso = \App\Models\Peso::where('user_id', $user->id)->first();

        $validated = $request->validate([
            'peso_first_name' => 'required|string|max:255',
            'peso_last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'contact_number' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:500',
            'social_links' => 'nullable|array',
            'social_links.facebook' => 'nullable|url',
            'social_links.twitter' => 'nullable|url',
            'social_links.linkedin' => 'nullable|url',
            'social_links.instagram' => 'nullable|url',
        ]);

        // Handle logo upload


        // Update PESO fields
        if ($peso) {
            $peso->update([
                'peso_first_name' => $validated['peso_first_name'],
                'peso_last_name' => $validated['peso_last_name'],
                'contact_number' => $validated['contact_number'],
                'description' => $validated['description'],
                'address' => $validated['address'] ?? null,
                'social_links' => isset($validated['social_links']) ? json_encode($validated['social_links']) : null,
            ]);
        }

        // Update email in users table if changed
        if ($user->email !== $validated['email']) {
            $user->email = $validated['email'];
            $user->save();
        }

        return back()->with('flash.banner', 'Profile updated successfully!');
    }


    public function updateLogo(Request $request)
{
    $user = Auth::user();
    $peso = \App\Models\Peso::where('user_id', $user->id)->first();

    $validated = $request->validate([
        'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Handle logo upload
    if ($request->hasFile('logo')) {
        if ($peso && $peso->logo) {
            Storage::disk('public')->delete($peso->logo);
        }
        $logoPath = $request->file('logo')->store('peso-logos', 'public');
        $peso->logo = $logoPath;
        $peso->save();
    }

    return back()->with('flash.banner', 'Profile picture updated successfully!');
}
}
