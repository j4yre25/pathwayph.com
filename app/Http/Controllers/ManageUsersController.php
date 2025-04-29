<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManageUsersController extends Controller
{
    public function index()
    {


        $all_users = User::all();

        return Inertia::render('Admin/ManageUsers/Index/Index', [
            'all_users' => $all_users


        ]);
    }

    public function list(Request $request)
    {
        $query = User::query();

        // role
        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        } else {
            $query->where('role', '!=', 'graduate');
        }

        // date
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }

        // status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_approved', $request->status === 'active');
        }

        $all_users = $query->get();

        return Inertia::render('Admin/ManageUsers/Index/List', [
            'all_users' => $all_users,
            'filters' => $request->only(['role', 'date_from', 'date_to', 'status']),
        ]);
    }

    public function archivedlist()
    {


        $all_users = User::onlyTrashed()->get();

        return Inertia::render('Admin/ManageUsers/Index/ArchivedList', [
            'all_users' => $all_users


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

        return redirect()->back()->with('flash.banner', 'User restored successfully.');
    }
}
