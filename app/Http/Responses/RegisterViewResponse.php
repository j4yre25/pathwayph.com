<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterViewResponse as RegisterViewResponseContract;
use Inertia\Inertia;

class RegisterViewResponse implements RegisterViewResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return Inertia::render('Auth/LandingPage', [
            'showLoginModal' => false,
            'showRegisterModal' => true,
        ]);
    }
}