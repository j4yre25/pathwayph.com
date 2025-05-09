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

}