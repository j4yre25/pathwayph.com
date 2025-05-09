<?php

namespace App\Http\Controllers;

use App\Models\Graduate;
use App\Models\Program;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB; // Added this
use Illuminate\Support\Facades\Auth; // Optional, if needed for auth()

class GraduateController extends Controller
{
    public function index()
    {
        $graduates = DB::table('graduates')
            ->join('users', 'graduates.user_id', '=', 'users.id')
            ->join('programs', 'graduates.program_id', '=', 'programs.id')
            ->join('school_years', 'graduates.school_year_id', '=', 'school_years.id')
            ->where('users.role', 'graduate')
            ->where('users.institution_id', Auth::user()->id)
            ->where('users.is_approved', true)
            ->select(
                'users.id as user_id',
                DB::raw("CONCAT(users.graduate_first_name, ' ', users.graduate_middle_initial, ' ', users.graduate_last_name) as full_name"),
                'programs.name as program_name',
                'school_years.school_year_range as year_graduated',
                'graduates.current_job_title',
                DB::raw("'Yes' as graduated")
            )
            ->orderBy('users.created_at', 'desc')
            ->get();

        $institutionPrograms = Program::where('institution_id', Auth::user()->id)->get();
        $institutionYears = SchoolYear::where('institution_id', Auth::user()->id)->get();
        $instiUsers = DB::table('users')
            ->where('role', 'institution')
            ->where('id', Auth::user()->id)
            ->get(['id', 'institution_name']);

        return Inertia::render('Graduates/Index', [
            'graduates' => $graduates,
            'programs' => $institutionPrograms,
            'years' => $institutionYears,
            'instiUsers' => $instiUsers,
        ]);
    }

    public function update(Request $request, Graduate $graduate)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_initial' => 'required|string|max:5',
            'program_id' => 'required|exists:programs,id',
            'graduate_year_graduated' => 'required|exists:school_years,school_year_range',
            'employment_status' => 'required|in:Employed,Underemployed,Unemployed',
            'current_job_title' => 'nullable|string|max:255',
        ]);

        // Convert the raw school year range to its corresponding ID.
        $schoolYearId = DB::table('school_years')
            ->where('school_year_range', $validated['graduate_year_graduated'])
            ->value('id');

        if ($validated['employment_status'] === 'Unemployed') {
            $validated['current_job_title'] = 'N/A';
        }

        // Prepare update data with the mapped school_year_id.
        $updateData = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_initial' => $validated['middle_initial'],
            'program_id' => $validated['program_id'],
            'school_year_id' => $schoolYearId,
            'employment_status' => $validated['employment_status'],
            'current_job_title' => $validated['current_job_title'],
        ];

        $graduate->update($updateData);

        return redirect()->back()->with('flash.banner', 'Graduate updated successfully.');
    }


    public function destroy(Graduate $graduate)
    {
        $graduate->delete();
        return redirect()->back()->with('flash.banner', 'Graduate archived successfully.');
    }
    
}
