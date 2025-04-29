<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Actions\Fortify\CreateNewAdmin;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminRegisterController extends Controller
{
    /**
     * Show the admin registration form.
     *
     * @return \Inertia\Response
     */
    public function showRegistrationForm()
    {
        return Inertia::render('Auth/AdminRegister');
    }

    /**
     * Handle an incoming admin registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Actions\Fortify\CreateNewAdmin  $creator
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request, CreateNewAdmin $creator)
    {
        $creator->create($request->all());

        redirect()->back()->with('flash.banner', 'Registered Successfully!');

    }
}
