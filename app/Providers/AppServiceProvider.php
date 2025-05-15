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
            'app' => [
                'currentUser' => function () {
                    $user = Auth::check()
                        ? \App\Models\User::with('company')->find(Auth::id())
                        : null;
                    return $user;
                },
            ],
        ]);
    }
}
