<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginViewResponse as LoginViewResponseContract;
use Inertia\Inertia;

class LoginViewResponse implements LoginViewResponseContract
{
    public function toResponse($request)
    {
        return Inertia::render('Auth/LandingPage', [
            'showLoginModal' => true,
            'showRegisterModal' => false,
        ]);
    }
}