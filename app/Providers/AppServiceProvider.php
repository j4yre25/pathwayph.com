<?php

namespace App\Providers;

use App\Models\Graduate;
use App\Models\HumanResource;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use App\Models\Messages;

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

               'messages' => function () {
                $user = Auth::user();
                if (!$user) return [];

                // Fetch recent received messages, then group by conversation (application_id)
                $rows = Messages::with(['sender', 'application.job.company'])
                    ->where('receiver_id', $user->id)
                    ->latest()
                    ->limit(200)
                    ->get();

                // Group by conversation
                $threads = $rows->groupBy('application_id')->map(function ($group) {
                    $last = $group->sortByDesc('created_at')->first();
                    $unread = $group->whereNull('read_at')->count();

                    $sender = $last->sender;

                    // Resolve company for avatar/company label (if sender is HR/company)
                    $company = $sender?->companyThroughHR ?? $sender?->company ?? $last->application?->job?->company;

                    // Avatar: company avatar if HR/company, else user profile for graduates
                    $companyAvatarPath = $company?->company_profile_photo_path ?: $company?->company_logo_path;
                    $senderAvatar = $companyAvatarPath
                        ? Storage::url($companyAvatarPath)
                        : (property_exists($sender, 'profile_photo_url') ? $sender->profile_photo_url : null);

                    // Resolve a better sender name
                    $displayName = $sender?->name;
                    if (($sender?->role ?? null) === 'graduate') {
                        // Try Graduate profile first
                        $grad = \App\Models\Graduate::select('first_name','last_name')->where('user_id', $sender->id)->first();
                        if ($grad) {
                            $displayName = trim($grad->first_name . ' ' . $grad->last_name);
                        } elseif (!empty($sender->graduate_first_name) || !empty($sender->graduate_last_name)) {
                            $displayName = trim(($sender->graduate_first_name ?? '') . ' ' . ($sender->graduate_last_name ?? ''));
                        }
                        if (!$displayName) $displayName = $sender->email ?? 'Applicant';
                    } elseif (($sender?->role ?? null) === 'company') {
                        // Prefer HR name; fallback to company name
                        $hr = $sender?->hr;
                        $displayName = $hr ? trim(($hr->first_name ?? '') . ' ' . ($hr->last_name ?? '')) : ($company?->company_name ?? $sender?->name);
                    } else {
                        // Fallbacks
                        $displayName = $displayName ?: ($company?->company_name ?: ($sender->email ?? 'User'));
                    }

                    return [
                        // For compatibility with the dropdown
                        'message_id' => $last->id, // last message id
                        'id' => $last->id,         // keep existing key usage
                        'conversation_id' => $last->application_id,
                        'sender_name' => $displayName,
                        'sender_company' => $company?->company_name,
                        'sender_avatar_url' => $senderAvatar,
                        'body' => $last->content,
                        'body_preview' => str($last->content)->limit(120)->toString(),
                        'created_at' => optional($last->created_at)->diffForHumans(),
                        'read_at' => $unread > 0 ? null : optional($last->read_at)?->toISOString(),
                        'unread_count' => $unread,
                        'link' => null,
                    ];
                });

                // Sort threads by latest message time desc
                return $threads
                    ->sortByDesc(fn ($t) => $rows->firstWhere('id', $t['message_id'])?->created_at?->timestamp ?? 0)
                    ->values();
            },
            'messages_count' => function () {
                $user = Auth::user();
                if (!$user) return 0;
                return Messages::where('receiver_id', $user->id)->whereNull('read_at')->count();
            },
        ]);
    }
}
