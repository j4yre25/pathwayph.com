<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::share([
        'auth' => [
            'user' => function () {
                $user = Auth::user();
                if ($user) {
                    $user->load('company');  // Eager load the company relationship
                    }
                    return $user;
                },
            ],
        ]);
    }
}
