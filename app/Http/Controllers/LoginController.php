<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'The email address is not registered.',
            ]);
        }

        // ✅ Block archived users
        if ($user->archived_at) {
            throw ValidationException::withMessages([
                'email' => 'Your account has been archived. Please contact the administrator.',
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'password' => 'The password you entered is incorrect.',
            ]);
        }

        $user = Auth::user();
        
        if ($user->role === 'company') {
            // Check if company or HR info is missing
            $hasCompany = $user->company()->exists();
            $hasHR = $user->hr()->exists();

            if (!$hasCompany || !$hasHR) {
                return redirect()->route('company.information');
            }

            // Optionally, check for approval status
            if (!$user->is_approved) {
                // You can redirect to dashboard and show a modal, or to a waiting page
                return redirect()->route('dashboard')->with('pending_approval', true);
            }
        }

        return redirect()->intended(route('dashboard'));
    }
}