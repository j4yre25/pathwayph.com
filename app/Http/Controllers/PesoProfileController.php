<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PesoProfileController extends Controller
{
    public function profile()
    {
        $user = Auth::user();

        return Inertia::render('Admin/PesoProfile', [
            'peso' => [
                'peso_first_name' => $user->peso_first_name,
                'peso_last_name' => $user->peso_last_name,
                'email' => $user->email,
                'contact_number' => $user->company_contact_number,
                'description' => $user->description,
            ],
        ]);
    }
}
