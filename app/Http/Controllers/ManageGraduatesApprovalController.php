<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ManageGraduatesApprovalController extends Controller
{
    public function index(Request $request)
    {
        $institution = Auth::user()->institution;
        if (!$institution) {
            return redirect()->back()->with('flash.banner', 'Your account is not linked to an institution.');
        }
        $institutionId = $institution->id;

        // Fetch all graduates for this institution without pagination
        $graduates = DB::table('graduates')
            ->join('users', 'graduates.user_id', '=', 'users.id')
            ->where('users.role', 'graduate')
            ->where('graduates.institution_id', $institutionId)
            ->where(function($q) {
                $q->where('users.is_approved', false)
                  ->orWhereNull('users.is_approved');
            })
            ->orderBy('users.created_at', 'desc')
            ->select('users.id as user_id', 'users.email', 'users.is_approved', 'graduates.first_name', 'graduates.middle_name', 'graduates.last_name')
            ->get()
            ->map(function($g) {
                return [
                    'id' => $g->user_id, // This is always the user's id
                    'email' => $g->email,
                    'name' => trim("{$g->first_name} {$g->middle_name} {$g->last_name}"),
                    'status' => $g->is_approved === null ? 'pending' : ($g->is_approved ? 'approved' : 'disapproved'),
                ];
            });

        // Get distinct programs for filtering options (ONLY programs for this institution)
        $programs = DB::table('graduates')
            ->join('users', 'graduates.user_id', '=', 'users.id')
            ->where('graduates.institution_id', $institutionId)
            ->where('users.role', 'graduate')
            ->select('graduates.program_id')
            ->distinct()
            ->pluck('program_id');

        return Inertia::render('Institutions/ManageGraduatesApproval/Index', [
            'graduates' => $graduates,
            'programs' => $programs,
            'filter_program' => $request->input('program', 'all'),
        ]);
    }

    /**
     * Approve a graduate and enable login.
     */
    public function approve(User $user)
    {
        $institution = Auth::user()->institution;
        if (!$institution) {
            return redirect()->back()->with('flash.banner', 'Your account is not linked to an institution.');
        }
        $institutionId = $institution->id;

        $graduate = DB::table('graduates')->where('user_id', $user->id)->first();

        if ($user->role !== 'graduate' || !$graduate || $graduate->institution_id != $institutionId) {
            return redirect()->back()->with('flash.banner', 'You can only approve graduates from your institution.');
        }

        $user->is_approved = true;
        $user->save();
        return redirect()->back()->with('flash.banner', 'Graduate approved successfully.');
    }

    public function disapprove(User $user)
    {
        $institution = Auth::user()->institution;
        if (!$institution) {
            return redirect()->back()->with('flash.banner', 'Your account is not linked to an institution.');
        }
        $institutionId = $institution->id;

        $graduate = DB::table('graduates')->where('user_id', $user->id)->first();
        if ($user->role !== 'graduate' || !$graduate || $graduate->institution_id != $institutionId) {
            return redirect()->back()->with('flash.banner', 'You can only disapprove graduates from your institution.');
        }

        $user->is_approved = false;
        $user->save();
        return redirect()->back()->with('flash.banner', 'Graduate disapproved successfully.');
    }

    public function list(Request $request)
    {
        $institutionId = Auth::user()->institution->id;

        $query = DB::table('graduates')
            ->join('users', 'graduates.user_id', '=', 'users.id')
            ->join('programs', 'graduates.program_id', '=', 'programs.id')
            ->where('users.role', 'graduate')
            ->where('graduates.institution_id', $institutionId)
            ->where('users.is_approved', true);

        $filters = $request->only(['program', 'date_from', 'date_to', 'status']);

        if (!empty($filters['program']) && $filters['program'] !== 'all') {
            $query->where('programs.name', '=', $filters['program']);
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            // Set date_to to end of day
            $dateFrom = $filters['date_from'] . ' 00:00:00';
            $dateTo = $filters['date_to'] . ' 23:59:59';
            $query->whereBetween('users.created_at', [$dateFrom, $dateTo]);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'inactive') {
                $query->whereNotNull('users.deleted_at');
            } else {
                $query->whereNull('users.deleted_at');
            }
        }

        // Use paginate instead of get
        $graduates = $query->orderBy('users.created_at', 'desc')
            ->select(
                'users.*',
                'graduates.*',
                'programs.name as program_name',
                DB::raw("IF(users.deleted_at IS NULL, 'Active', 'Inactive') as status")
            )
            ->paginate(20)
            ->withQueryString();

        $programs = DB::table('graduates')
            ->join('programs', 'graduates.program_id', '=', 'programs.id')
            ->where('graduates.institution_id', $institutionId)
            ->distinct()
            ->pluck('programs.name');

        return Inertia::render('Institutions/ManageGraduatesApproval/List', [
            'graduates' => $graduates,
            'programs' => $programs,
            'filters' => $filters,
        ]);
    }
}
