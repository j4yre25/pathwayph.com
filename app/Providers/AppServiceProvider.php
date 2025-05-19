<?php

namespace App\Providers;

use App\Models\HumanResource;
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
            'auth' => function() {
                return [
                    'user' => Auth::user() ? Auth::user()->load('hr') : null,
                ];
            },
            'roles' => function () {
                $user = Auth::user()?->load('hr');;
                return [
                    'isCompany' => $user->role === 'company',
                ];
            },
            'main' => function () {
                return [
                    'is_main_hr' => Auth::user()?->hr?->is_main_hr === true,
                ];
            },
            'hrCount' => function () {
                $user = Auth::user();
                return $user && $user->hr
                    ? HumanResource::where('company_id', $user->hr->company_id)->count()
                    : 0;
            },
            'app' => [
                'currentUser' => fn () => Auth::check()
                    ? \App\Models\User::with('company')->find(Auth::id())
                    : null,
            ],
        ]);
    }
}
