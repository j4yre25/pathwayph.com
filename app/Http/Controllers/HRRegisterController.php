<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Actions\Fortify\CreateNewHR;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HRRegisterController extends Controller
{
    /**
     * Show the admin registration form.
     *
     * @return \Inertia\Response
     */
    public function showRegistrationForm()
    {
        return Inertia::render('Auth/HResourceRegister');
    }

    /**
     * Handle an incoming hr registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Actions\Fortify\CreateNewHR  $creator
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request, CreateNewHR $creator)
    {
        $creator->create($request->all());

        return redirect()->route('dashboard')->with('success', 'Registration successful!');
    }
}
