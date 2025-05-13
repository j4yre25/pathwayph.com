<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewGraduate;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GraduateRegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return Inertia::render('Auth/RegisterGraduate');
    }

    /**
     * Handle an incoming admin registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Actions\Fortify\CreateNewAdmin  $creator
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request, CreateNewGraduate $creator)
    {
        $creator->create($request->all());

        return redirect()->route('dashboard')->with('success', 'Graduate registration successful!');
    }

    public function getUsers()
    {
        $insti_users = User::all();

        // Return the users data using Inertia
        return Inertia::render('Auth/Register', [
            'insti_users' => $insti_users
        ]);
    }   
}
