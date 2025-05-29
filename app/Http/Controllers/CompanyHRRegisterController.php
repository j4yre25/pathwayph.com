<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Actions\Fortify\CreateNewHR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CompanyHRRegisterController extends Controller
{
    /**
     * Show the admin registration form.
     *
     * @return \Inertia\Response
     */

     public function __construct()
    {
        
    }
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

       return redirect()
            ->route('company.manage-hrs')
            ->with('flash.banner', 'HR account successfully registered.');

    }
}
