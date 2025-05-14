<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManageUsersController extends Controller
{
    public function index()
    {


        $users = User::whereIn('role', ['company', 'institution', 'peso'])
            ->orderBy('created_at', 'desc') // Sort by created_at in descending order
            ->paginate(10); // Paginate the results

            // Append full name depending on role
        $users->getCollection()->transform(function ($user) {
            if ($user->role === 'company') {
                $user->full_name = trim("{$user->company_hr_first_name} {$user->company_hr_last_name}");
                $user->organization_name = $user->company->company_name ?? '-';
            } elseif ($user->role === 'institution') {
                $user->full_name = trim("{$user->institution_career_officer_first_name} {$user->institution_career_officer_last_name}");
                $user->organization_name = $user->institution_name ?? '-';
            } elseif ($user->role === 'peso') {
                $user->full_name = trim("{$user->peso_first_name} {$user->peso_last_name}");
                $user->organization_name = $user->peso_name ?? '-';
            } elseif ($user->role === 'graduate') {
                $user->full_name = trim("{$user->graduate_first_name} {$user->graduate_last_name}");
            } else {
                $user->full_name = 'N/A';
            }

            return $user;
        });

        return Inertia::render('Admin/ManageUsers/Index/Index', [
            'all_users' => $users


        ]);
    }

    public function list(Request $request)
    {
        $users = User::query()
            ->when($request->role && $request->role !== 'all', function ($query) use ($request) {
                $query->where('role', $request->role);
            })
            ->when($request->date_from, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->date_to, function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->when($request->status && $request->status !== 'all', function ($query) use ($request) {
                $query->where('is_approved', $request->status === 'active');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Paginate the results (10 users per page)

        
        $users->getCollection()->transform(function ($user) {
            if ($user->role === 'company') {
                $user->full_name = trim("{$user->company_hr_first_name} {$user->company_hr_last_name}");
                $user->organization_name = $user->company->company_name ?? '-';
            } elseif ($user->role === 'institution') {
                $user->full_name = trim("{$user->institution_career_officer_first_name} {$user->institution_career_officer_last_name}");
                $user->organization_name = $user->institution_name ?? '-';
            } elseif ($user->role === 'peso') {
                $user->full_name = trim("{$user->peso_first_name} {$user->peso_last_name}");
                $user->organization_name = $user->peso_name ?? '-';
            } elseif ($user->role === 'graduate') {
                $user->full_name = trim("{$user->graduate_first_name} {$user->graduate_last_name}");
            } else {
                $user->full_name = 'N/A';
            }

            return $user;
        });

        return inertia('Admin/ManageUsers/Index/List', [
            'all_users' => $users,
        ]);
    }

    public function archivedlist()
    {

        $users = User::onlyTrashed()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/ManageUsers/Index/ArchivedList', [
            'all_users' => $users


        ]);
    }


    public function edit(User $users)
    {
        return Inertia::render('Admin/ManageUsers/Edit/Index', [
            'user' => $users
        ]);
    }



    public function approve(User $user)
    {
        $user->is_approved = true;

        if ($user->role === 'company') {
            $user->is_main_hr = true;
        }
        
        $user->save();

        return redirect()->route('admin.manage_users')->with('flash.banner', 'User  approved successfully.');
    }

    public function disapprove(User $user)
    {
        $user->is_approved = false;
        $user->save();

        return redirect()->back()->with('success', 'User disapproved successfully.');
    }

    public function delete(Request $request, User $user)
    {

        // Gate::authorize('delete', $job);

        $user->delete();

        // $user_id = $request->user()->id;

        return redirect()->route('admin.manage_users')->with('flash.banner', 'User archived successfully.');
    }

    public function restore($user)
    {
        $user = User::withTrashed()->findOrFail($user);

        $user->restore();

        return redirect()->route('admin.manage_users')->with('flash.banner', 'User restored successfully.');
    }
}
