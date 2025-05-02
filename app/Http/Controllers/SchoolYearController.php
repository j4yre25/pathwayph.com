<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class SchoolYearController extends Controller
{
    public function index(User $user)
    {
        $user->load('school_years');

        return Inertia::render('Institutions/SchoolYears/Index', [
            'school_years' => $user->school_years
        ]);
    }

    public function list(Request $request)
    {
        $status = $request->input('status', 'all');

        $school_years = SchoolYear::with('user')->withTrashed()
            ->when($status === 'active', fn($query) => $query->whereNull('deleted_at'))
            ->when($status === 'inactive', fn($query) => $query->whereNotNull('deleted_at'))
            ->get();

        return Inertia::render('Institutions/SchoolYears/List', [
            'school_years' => $school_years,
            'status' => $status,
        ]);
    }

    public function archivedList()
    {
        $all_schoolYears = SchoolYear::with('user')->onlyTrashed()->get();

        return Inertia::render('Institutions/SchoolYears/ArchivedList', [
            'all_school_years' => $all_schoolYears
        ]);
    }

    public function create(User $user)
    {
        return Inertia::render('Institutions/SchoolYears/Create');
    }

    public function store(Request $request, User $user)
    {
        $request->validate([
            'school_year_range' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'term' => ['required', 'integer', 'between:1,5'],
        ]);

        [$start, $end] = explode('-', $request->school_year_range);
        if ((int) $start >= (int) $end) {
            return back()->withErrors(['school_year_range' => 'Invalid range (start year must be less than end year).']);
        }

        $exists = SchoolYear::withTrashed()
            ->where('user_id', $user->id)
            ->where('school_year_range', $request->school_year_range)
            ->where('term', $request->term)
            ->exists();

        if ($exists) {
            return back()->with(['flash.banner' => 'This school year and term already exists.']);
        }

        SchoolYear::create([
            'school_year_range' => $request->school_year_range,
            'term' => $request->term,
            'user_id' => $user->id,
        ]);

        return redirect()->back()->with('flash.banner', 'School year added.');
    }

    public function edit(SchoolYear $schoolYear)
    {
        return Inertia::render('Institutions/SchoolYears/Edit', [
            'schoolYear' => $schoolYear
        ]);
    }

    public function update(Request $request, SchoolYear $schoolYear)
    {

        $request->validate([
            'school_year_range' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'term' => ['required', 'integer', 'between:1,5'],
        ]);

        [$start, $end] = explode('-', $request->school_year_range);
        if ((int) $start >= (int) $end) {
            return back()->withErrors(['school_year_range' => 'Invalid range (start year must be less than end year).']);
        }

        $exists = SchoolYear::withTrashed()
            ->where('user_id', $schoolYear->user_id)
            ->where('school_year_range', $request->school_year_range)
            ->where('term', $request->term)
            ->where('id', '!=', $schoolYear->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['flash.banner' => 'This entry already exists.']);
        }

        $schoolYear->update([
            'school_year_range' => $request->school_year_range,
            'term' => $request->term,
        ]);

        return redirect()->back()->with('flash.banner', 'School year updated.');
    }

    public function delete(Request $request, SchoolYear $schoolYear)
    {
        $schoolYear->delete();

        $user_id = $request->user()->id;

        return redirect(route('school-years', ['user' => $user_id]))->with('flash.banner', 'School year archived.');
    }

    public function restore($schoolYear)
    {
        $schoolYear = SchoolYear::withTrashed()->findOrFail($schoolYear);
        $schoolYear->restore();

        return redirect()->back()->with('flash.banner', 'School year restored.');
    }
}
