<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewCareerOfficer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CareerOfficerRegisterController extends Controller
{
    /**
     * Show the admin registration form.
     *
     * @return \Inertia\Response
     */
    public function showRegistrationForm()
    {
        return Inertia::render('Auth/CareerOfficerRegister');
    }

    /**
     * Handle an incoming hr registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Actions\Fortify\CreateNewHR  $creator
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request, CreateNewCareerOfficer $creator)
    {
        $creator->create($request->all());

        return redirect()->route('dashboard')->with('success', 'Registration successful!');
    }
}
