<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RegisterChoiceController  extends Controller
{
    public function profile()
    {

        return Inertia::render('Auth/PathwayRegister', [

        ]);
    }
}
