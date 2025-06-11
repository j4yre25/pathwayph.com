<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManageUsersController extends Controller
{
    public function index()
    {
        $users = User::with([
            'company',       // relationship: company()
            'institution',   // relationship: institution()
            'peso',          // relationship: peso()
            'graduate',      // relationship: graduate()
        ])
            ->whereIn('role', ['company', 'institution', 'peso', 'graduate'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Append full name and organization name using related models
        $users->getCollection()->transform(function ($user) {
            switch ($user->role) {
                case 'company':
                    $user->full_name = $user->company
                        ? trim("{$user->company->first_name} {$user->company->last_name}")
                        : 'N/A';
                    $user->organization_name = ($user->company && !empty($user->company->company_name))
                        ? $user->company->company_name
                        : '-';
                    break;
                case 'institution':
                    $user->full_name = $user->institution
                        ? trim("{$user->institution->institution_president_first_name} {$user->institution->institution_president_last_name}")
                        : 'N/A';
                    $user->organization_name = $user->institution->institution_name ?? '-';
                    break;
                case 'peso':
                    $user->full_name = $user->peso
                        ? trim("{$user->peso->peso_first_name} {$user->peso->peso_last_name}")
                        : 'N/A';
                    $user->organization_name = $user->peso->peso_name ?? '-';
                    break;
                case 'graduate':
                    $user->full_name = $user->graduate
                        ? trim("{$user->graduate->first_name} {$user->graduate->last_name}")
                        : 'N/A';
                    $user->organization_name = '-';
                    break;
                default:
                    $user->full_name = 'N/A';
                    $user->organization_name = '-';
            }
            return $user;
        });

        return Inertia::render('Admin/ManageUsers/Index/Index', [
            'all_users' => $users
        ]);
    }

    public function list(Request $request)
    {
        $users = User::with([
            'company',
            'institution',
            'peso',
            'graduate',
        ])
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
            ->paginate(10);

        $users->getCollection()->transform(function ($user) {
            switch ($user->role) {
                case 'company':
                    $user->full_name = $user->company
                        ? trim("{$user->company->first_name} {$user->company->last_name}")
                        : 'N/A';
                    $user->organization_name = $user->company->company_name ?? '-';
                    break;
                case 'institution':
                    $user->full_name = $user->institution
                        ? trim("{$user->institution->institution_president_first_name} {$user->institution->institution_president_last_name}")
                        : 'N/A';
                    $user->organization_name = $user->institution->institution_name ?? '-';
                    break;
                case 'peso':
                    $user->full_name = $user->peso
                        ? trim("{$user->peso->peso_first_name} {$user->peso->peso_last_name}")
                        : 'N/A';
                    $user->organization_name = $user->peso->peso_name ?? '-';
                    break;
                case 'graduate':
                    $user->full_name = $user->graduate
                        ? trim("{$user->graduate->first_name} {$user->graduate->last_name}")
                        : 'N/A';
                    $user->organization_name = '-';
                    break;
                default:
                    $user->full_name = 'N/A';
                    $user->organization_name = '-';
            }
            return $user;
        });

        return inertia('Admin/ManageUsers/Index/List', [
            'all_users' => $users,
        ]);
    }

    public function archivedlist()
    {
        $users = User::with([
            'company',
            'institution',
            'peso',
            'graduate',
        ])
            ->onlyTrashed()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $users->getCollection()->transform(function ($user) {
            switch ($user->role) {
                case 'company':
                    $user->full_name = $user->company
                        ? trim("{$user->company->first_name} {$user->company->last_name}")
                        : 'N/A';
                    $user->organization_name = $user->company->company_name ?? '-';
                    break;
                case 'institution':
                    $user->full_name = $user->institution
                        ? trim("{$user->institution->institution_president_first_name} {$user->institution->institution_president_last_name}")
                        : 'N/A';
                    $user->organization_name = $user->institution->institution_name ?? '-';
                    break;
                case 'peso':
                    $user->full_name = $user->peso
                        ? trim("{$user->peso->peso_first_name} {$user->peso->peso_last_name}")
                        : 'N/A';
                    $user->organization_name = $user->peso->peso_name ?? '-';
                    break;
                case 'graduate':
                    $user->full_name = $user->graduate
                        ? trim("{$user->graduate->first_name} {$user->graduate->last_name}")
                        : 'N/A';
                    $user->organization_name = '-';
                    break;
                default:
                    $user->full_name = 'N/A';
                    $user->organization_name = '-';
            }
            return $user;
        });

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

        // If company, set as main HR
        if ($user->role === 'company' && $user->hr) {
            $user->hr->is_main_hr = true;
            $user->hr->save();
        }

        $verificationCode = rand(100000, 999999);
        $user->verification_code = $verificationCode;
        $user->save();

        // Send the verification code via email
        $user->notify(new \App\Notifications\VerifyEmailWithCode($verificationCode));

        return redirect()->route('admin.manage_users')->with('flash.banner', 'User approved and verification code sent successfully.');
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
