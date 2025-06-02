<?php

namespace App\Providers;

use App\Models\Graduate;
use App\Models\HumanResource;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\ServiceProvider;

use Laravel\Fortify\Contracts\LoginResponse;
use App\Actions\Fortify\LoginResponse as CustomLoginResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /** @var \App\Models\User $user */

        Inertia::share([
            'auth' => function () {
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
                    : 3;
            },
            'app' => [
                'currentUser' => fn() => Auth::check()
                    ? \App\Models\User::with('company')->find(Auth::id())
                    : null,
            ],
            'graduate' => fn() => Auth::check()
                ? Graduate::where('user_id', Auth::id())->first()
                : null,



        ]);
    }
}
