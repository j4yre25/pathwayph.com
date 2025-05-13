<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function getUsers()
    {
        $insti_users = User::all();


        // Return the users data using Inertia
        return Inertia::render('Auth/RegisterGraduateSample', [
            'insti_users' => $insti_users

        ]);
    }
}