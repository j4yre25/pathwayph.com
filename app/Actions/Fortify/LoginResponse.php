<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Auth;


class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();
        if ($user) {
            $user->update([
                'last_login_at' => now(),
            ]);
        }
        return redirect()->intended(Fortify::redirects('login'));
    }
}
