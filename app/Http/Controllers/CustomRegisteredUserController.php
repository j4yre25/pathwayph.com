<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\Program;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use Laravel\Fortify\Fortify;
use App\Models\User; // Make sure to import your User model
use App\Notifications\VerifyEmailWithCode;
use Inertia\Inertia;

class CustomRegisteredUserController extends Controller
{
    protected $guard;

    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    public function create(Request $request): RegisterViewResponse
    {
        return app(RegisterViewResponse::class);
    }

    public function store(Request $request, CreatesNewUsers $creator)
    {
        if (config('fortify.lowercase_usernames') && $request->has(Fortify::username())) {
            $request->merge([
                Fortify::username() => Str::lower($request->{Fortify::username()}),
            ]);
        }

        $role = $this->determineRole($request);
        $user = $creator->create(array_merge($request->all(), ['role' => $role]));


         // Generate a verification code
         $verificationCode = rand(100000, 999999);

    // Store the verification code in the database (e.g., in a `verification_code` column)
        $user->verification_code = $verificationCode;
        $user->save();

    // Send the verification code via email
        $user->notify(new VerifyEmailWithCode($verificationCode));

        
        event(new Registered($user));

        return redirect()->back()->with('flash.banner', 'Registered Successfully!');
    }

    protected function determineRole(Request $request): string
    {
        if ($request->is('register/graduate')) {
            return 'graduate';
        } elseif ($request->is('register/company')) {
            return 'company';
        } elseif ($request->is('register/institution')) {
            return 'institution';
        }

        return 'guest';
    }

    public function showGraduateDetails()
{
    $insti_users = User::where('role', 'institution')->get(['id', 'institution_name']);
    
    // Retrieve programs per institution instead of globally
    $programs = Program::whereIn('institution_id', $insti_users->pluck('id'))->get();

    // Retrieve school years per institution instead of globally
    $school_years = SchoolYear::whereIn('institution_id', $insti_users->pluck('id'))->get();

    return Inertia::render('Auth/Register', [
        'insti_users' => $insti_users,
        'programs' => $programs,
        'school_years' => $school_years
    ]);
}

public function verifyEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return redirect()->back()->withErrors(['email' => 'Email not found.']);
    }

    // Generate a verification code
    $verificationCode = rand(100000, 999999);

    // Save the verification code to the user
    $user->verification_code = $verificationCode;
    $user->save();

    // Send the verification code via email
    $user->notify(new VerifyEmailWithCode($verificationCode));

    return back()->with('message', 'Verification code sent to your email.');
}

public function verifyCode(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'verification_code' => 'required|numeric',
    ]);

    $user = User::where('email', $request->email)
        ->where('verification_code', $request->verification_code)
        ->first();

    if (!$user) {
        return redirect()->back()->withErrors(['verification_code' => 'Invalid verification code.']);
    }

    $user->email_verified_at = now();
    $user->verification_code = null; // Clear the verification code
    $user->is_approved = true;
    $user->save();

    return redirect()->route('login')->with('message', 'Email verified successfully! You can now log in.');
}
public function resendVerificationCode(Request $request)
{
    $user = $request->user();

    // Generate a new verification code
    $verificationCode = rand(100000, 999999);
    $user->verification_code = $verificationCode;
    $user->save();

    // Send the verification code via email
    $user->notify(new VerifyEmailWithCode($verificationCode));

    return back()->with('message', 'Verification code sent!');
}
}