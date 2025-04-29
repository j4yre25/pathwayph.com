<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
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

class  CustomRegisteredUserController extends Controller
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Show the registration view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Fortify\Contracts\RegisterViewResponse
     */
    public function create(Request $request): RegisterViewResponse
    {
        return app(RegisterViewResponse::class);
    }

    /**
     * Create a new registered user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Fortify\Contracts\CreatesNewUsers  $creator
     * @return \Laravel\Fortify\Contracts\RegisterResponse
     */
    public function store(Request $request, CreatesNewUsers $creator)
    {
        if (config('fortify.lowercase_usernames') && $request->has(Fortify::username())) {
            $request->merge([
                Fortify::username() => Str::lower($request->{Fortify::username()}),
            ]);
        }

        // Determine the role based on the route or request
        $role = $this->determineRole($request);

        // Pass the role to the creator
        $user = $creator->create(array_merge($request->all(), ['role' => $role]));

        event(new Registered($user));

        // $this->guard->login($user, $request->boolean('remember'));

        redirect()->back()->with('flash.banner', 'Registered Successfully!');
        }

    /**
     * Determine the role based on the route or request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
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
        $insti_users = User::all();
        $school_year = SchoolYear::all();
        
        return Inertia::render('Auth/Register', [
            'insti_users' => $insti_users,
            'school_year' => $school_year,
        ]);
    }
   



}

