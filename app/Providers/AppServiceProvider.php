<?php

namespace App\Providers;

use App\Models\Graduate;
use App\Models\HumanResource;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\ServiceProvider;

use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use App\Actions\Fortify\LoginResponse as CustomLoginResponse;
use App\Http\Responses\LoginViewResponse as CustomLoginViewResponse;
use App\Http\Responses\RegisterViewResponse as CustomRegisterViewResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);
        $this->app->singleton(LoginViewResponse::class, CustomLoginViewResponse::class);
        $this->app->singleton(RegisterViewResponse::class, CustomRegisterViewResponse::class);
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /** @var \App\Models\User $user */

        Inertia::share([
            'auth' => function () {
                $user = Auth::user();
                return [
                    'user' => $user ? $user->load(['company', 'hr']) : null,

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

            'needsApproval' => function () {
                $user = Auth::user();
                if (!$user) return false;
                if ($user->role === 'peso') return false;
                return !$user->is_approved && $user->has_completed_information;
            },  

            'notifications' => function () {
                $user = \Auth::user();
                if (!$user) return [];
                return $user->notifications()
                    ->latest()
                    ->take(20)
                    ->get()
                    ->map(function ($n) {
                        $d = $n->data ?? [];
                        // Autopopulate for legacy job_invite rows
                        if (($d['status'] ?? null) === 'job_invite') {
                            $d['title'] = $d['title'] ?? ('Job Invitation: ' . ($d['job_title'] ?? 'Position'));
                            $d['body']  = $d['body']  ?? ($d['message'] ?? ('You have been invited to apply for "' . ($d['job_title'] ?? 'the job') . '".'));
                            $d['link']  = $d['link']  ?? (isset($d['job_id']) ? url('/jobs/' . $d['job_id']) : null);
                        }
                        if (str_contains($n->type, 'ApplicationStatusUpdated')) {
                            $statusTxt = ucfirst(str_replace('_',' ', $d['status'] ?? 'updated'));
                            $d['title'] = $d['title'] ?? ('Application ' . $statusTxt);
                            $d['body']  = $d['body']  ?? ($d['message'] ?? '');
                        }
                        return [
                            'id' => $n->id,
                            'title' => $d['title'] ?? 'Notification',
                            'body' => $d['body'] ?? ($d['message'] ?? ''),
                            'status' => $d['status'] ?? null,
                            'job_id' => $d['job_id'] ?? null,
                            'link' => $d['link'] ?? null,
                            'read_at' => $n->read_at,
                            'created_at' => $n->created_at->diffForHumans(),
                        ];
                    });
            },
            'notifications_count' => fn() => \Auth::user()?->unreadNotifications()->count() ?? 0,


        ]);
    }
}
