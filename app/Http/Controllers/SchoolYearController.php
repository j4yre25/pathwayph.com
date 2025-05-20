<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\InstitutionSchoolYear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SchoolYearController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $institution = \App\Models\Institution::where('user_id', $user->id)->first();
        $schoolYears = InstitutionSchoolYear::with('schoolYear')
            ->where('institution_id', $institution?->id)
            ->get();

        return Inertia::render('Institutions/SchoolYears/Index', [
            'school_years' => $schoolYears,
        ]);
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $institution = \App\Models\Institution::where('user_id', $user->id)->first();
        $status = $request->input('status', 'all');

        $query = InstitutionSchoolYear::with('schoolYear')
            ->withTrashed()
            ->where('institution_id', $institution?->id);

        if ($status === 'active') {
            $query->whereNull('deleted_at');
        } elseif ($status === 'inactive') {
            $query->whereNotNull('deleted_at');
        }

        $schoolYears = $query->get();

        return Inertia::render('Institutions/SchoolYears/List', [
            'school_years' => $schoolYears,
            'status' => $status,
        ]);
    }

    public function archivedList()
    {
        $user = Auth::user();
        $institution = \App\Models\Institution::where('user_id', $user->id)->first();
        $archived = InstitutionSchoolYear::with('schoolYear')
            ->onlyTrashed()
            ->where('institution_id', $institution?->id)
            ->get();

        return Inertia::render('Institutions/SchoolYears/ArchivedList', [
            'all_school_years' => $archived
        ]);
    }

    public function create()
    {
        return Inertia::render('Institutions/SchoolYears/Create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $institution = \App\Models\Institution::where('user_id', $user->id)->first();
        if (!$institution) {
            return back()->withErrors(['institution' => 'Institution not found for this user.']);
        }

        $request->validate([
            'school_year_range' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'term' => ['required', 'integer', 'between:1,5'],
        ]);

        [$start, $end] = explode('-', $request->school_year_range);
        if ((int) $start >= (int) $end) {
            return back()->withErrors(['school_year_range' => 'Invalid range (start year must be less than end year).']);
        }

        // Check if school year exists globally
        $schoolYear = SchoolYear::withTrashed()
            ->where('school_year_range', $request->school_year_range)
            ->first();

        if (!$schoolYear) {
            $schoolYear = SchoolYear::create([
                'school_year_range' => $request->school_year_range,
            ]);
        }

        // Check if this institution already has this school year/term
        $exists = InstitutionSchoolYear::withTrashed()
            ->where('institution_id', $institution->id)
            ->where('school_year_range_id', $schoolYear->id)
            ->where('term', $request->term)
            ->exists();

        if ($exists) {
            return back()->with(['flash.banner' => 'This school year and term already exists for your institution.']);
        }

        InstitutionSchoolYear::create([
            'school_year_range_id' => $schoolYear->id,
            'term' => $request->term,
            'institution_id' => $institution->id,
        ]);

        return redirect()->back()->with('flash.banner', 'School year added.');
    }

    public function edit($id)
    {
        $user = Auth::user();
        $institution = \App\Models\Institution::where('user_id', $user->id)->first();
        $schoolYear = InstitutionSchoolYear::with('schoolYear')
            ->where('institution_id', $institution?->id)
            ->findOrFail($id);

        return Inertia::render('Institutions/SchoolYears/Edit', [
            'schoolYear' => $schoolYear
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $institution = \App\Models\Institution::where('user_id', $user->id)->first();

        $request->validate([
            'school_year_range' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'term' => ['required', 'integer', 'between:1,5'],
        ]);

        [$start, $end] = explode('-', $request->school_year_range);
        if ((int) $start >= (int) $end) {
            return back()->withErrors(['school_year_range' => 'Invalid range (start year must be less than end year).']);
        }

        $schoolYear = SchoolYear::withTrashed()
            ->where('school_year_range', $request->school_year_range)
            ->first();

        if (!$schoolYear) {
            $schoolYear = SchoolYear::create([
                'school_year_range' => $request->school_year_range,
            ]);
        }

        $exists = InstitutionSchoolYear::withTrashed()
            ->where('institution_id', $institution->id)
            ->where('school_year_range_id', $schoolYear->id)
            ->where('term', $request->term)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['flash.banner' => 'This entry already exists for your institution.']);
        }

        $institutionSchoolYear = InstitutionSchoolYear::where('institution_id', $institution->id)->findOrFail($id);

        $institutionSchoolYear->update([
            'school_year_range_id' => $schoolYear->id,
            'term' => $request->term,
        ]);

        return redirect()->back()->with('flash.banner', 'School year updated.');
    }

    public function delete(Request $request, $id)
    {
        $user = Auth::user();
        $institution = \App\Models\Institution::where('user_id', $user->id)->first();
        $schoolYear = InstitutionSchoolYear::where('institution_id', $institution?->id)->findOrFail($id);
        $schoolYear->delete();

        return back()->with('flash.banner', 'School Year archived.');
    }

    public function restore($id)
    {
        $user = Auth::user();
        $institution = \App\Models\Institution::where('user_id', $user->id)->first();
        $schoolYear = InstitutionSchoolYear::withTrashed()
            ->where('institution_id', $institution?->id)
            ->findOrFail($id);
        $schoolYear->restore();

        return redirect()->back()->with('flash.banner', 'School year restored.');
    }
}
