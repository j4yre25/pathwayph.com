<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB; // Added this
use Illuminate\Support\Facades\Auth; // Optional, if needed for auth()


class ManageGraduatesApprovalController extends Controller
{

    public function index(Request $request)
    {
        // Fetch all graduates for this institution without pagination
        $graduates = User::where('role', 'graduate')
            ->whereNotNull('institution_id')
            ->where('institution_id', Auth::user()->id)
            ->where('is_approved', false)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get distinct programs for filtering options (ONLY programs for this institution)
        $programs = User::where('role', 'graduate')
            ->where('institution_id', Auth::user()->id)
            ->select('graduate_program_completed')
            ->distinct()
            ->pluck('graduate_program_completed');

        return Inertia::render('Institutions/ManageGraduatesApproval/Index', [
            'graduates' => $graduates, // ✅ Now all graduates are passed without pagination!
            'programs' => $programs,
            'filter_program' => $request->input('program', 'all'),
        ]);

    }

    /**
     * Approve a graduate and enable login.
     */
    public function approve(User $user)
    {
        if ($user->role !== 'graduate' || $user->institution_id !== Auth::user()->id) {
            return redirect()->back()->with('flash.banner', 'You can only approve graduates from your institution.');
        }

        $user->is_approved = true;
        $user->save();

        return redirect()->back()->with('flash.banner', 'Graduate approved successfully.');
    }

    public function disapprove(User $user)
    {
        if ($user->role !== 'graduate' || $user->institution_id !== Auth::user()->id) {
            return redirect()->back()->with('flash.banner', 'You can only disapprove graduates from your institution.');
        }

        $user->delete();

        return redirect()->back()->with('flash.banner', 'Graduate disapproved successfully.');
    }
    public function list(Request $request)
{
    // Fetch graduates joined with users and programs
    $query = DB::table('graduates')
        ->join('users', 'graduates.user_id', '=', 'users.id')
        ->join('programs', 'graduates.program_id', '=', 'programs.id')
        ->where('users.role', 'graduate')
        ->where('users.institution_id', Auth::user()->id)
        ->where('users.is_approved', true); // ✅ Exclude pending accounts

    // Apply filters dynamically
    $filters = $request->only(['program', 'date_from', 'date_to', 'status']);
    
    if (!empty($filters['program']) && $filters['program'] !== 'all') {
        $query->where('programs.name', '=', $filters['program']);
    }
    
    if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
        $query->whereBetween('users.created_at', [$filters['date_from'], $filters['date_to']]);
    }
    
    if (!empty($filters['status']) && $filters['status'] !== 'all') {
        $query->where('users.deleted_at', $filters['status'] === 'inactive' ? '!=' : '=', null);
    }

    // Fetch graduates with computed status
    $graduates = $query->orderBy('users.created_at', 'desc')
        ->select('users.*', 'graduates.*', 'programs.name as program_name',
            DB::raw("IF(users.deleted_at IS NULL, 'Active', 'Inactive') as status"))
        ->get();

    // Retrieve distinct program names for filters
    $programs = DB::table('programs')
        ->where('institution_id', Auth::user()->id)
        ->distinct()
        ->pluck('name');

    return Inertia::render('Institutions/ManageGraduatesApproval/List', compact('graduates', 'programs', 'filters'));
}

}
