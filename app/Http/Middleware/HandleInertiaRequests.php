<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'roles' => [
                'isPeso' => fn () => $request->user()?->hasRole('peso'),
                'isInstitution' => fn () => $request->user()?->hasRole('institution'),
                'isCompany' => fn () => $request->user()?->hasRole('company'),
                'isGraduate' => fn () => $request->user()?->hasRole('graduate'),
            ],
            'permissions' => [
                'canManageUsers' => fn () => $request->user()
                ? $request->user()->hasPermissionTo('manage users'): null,
                'canManageApprovalGraduate' => fn () => $request->user()
                ? $request->user()->hasPermissionTo('manage approval graduate'): null,
                'canManageGraduate' => fn () => $request->user()
                ? $request->user()->hasPermissionTo('manage graduate'): null,
        
                'canManageInstitution' => fn () => $request->user()
                ? $request->user()->hasPermissionTo('manage institution'): null,

                'addSectors' => fn () => $request->user()
                ? $request->user()->hasPermissionTo('add sectors'): null,
                'updateSectors' => fn () => $request->user()
                ? $request->user()->hasPermissionTo('update sectors'): null,
                'deleteSectors' => fn () => $request->user()
                ? $request->user()->hasPermissionTo('delete sectors'): null,
             
                'viewSectors' => fn () => $request->user()
                ? $request->user()->hasPermissionTo('view sectors'): null,
             
            ],
            'csrf_token' => csrf_token(),
            'userNotApproved' => session('user_not_approved')
            
            //
        ]);
    }
    public function handle($request, \Closure $next)
{
    // Example: Restrict access to routes for non-PESO users
    if ($request->routeIs('admin.manage_users.*') && !$request->user()?->hasRole('peso')) {
        abort(403, 'Unauthorized');
    }

    return parent::handle($request, $next);
}
}
