<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SchoolYearController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('status', 'active');

        $query = SchoolYear::query();

        if ($filter === 'inactive') {
            $query->onlyTrashed();
        }

        $schoolYears = $query->orderBy('school_year_range')->get();

        return Inertia::render('Institutions/SchoolYearTab', [
            'schoolYears' => $schoolYears,
            'filter' => $filter,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSchoolYear($request);

        // Avoid duplicates
        if (SchoolYear::where('school_year_range', $validated['school_year_range'])
            ->where('term', $validated['term'])
            ->exists()) {
            throw ValidationException::withMessages([
                'school_year_range' => 'This school year and term combination already exists.',
            ]);
        }

        SchoolYear::create($validated);

        return redirect()->route('school-years.index')->with('success', 'School year added successfully.');
    }

    public function update(Request $request, SchoolYear $schoolYear)
    {
        $validated = $this->validateSchoolYear($request);

        // Avoid duplicates excluding current
        if (SchoolYear::where('school_year_range', $validated['school_year_range'])
            ->where('term', $validated['term'])
            ->where('id', '!=', $schoolYear->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'school_year_range' => 'Another entry with this year and term already exists.',
            ]);
        }

        $schoolYear->update($validated);

        return redirect()->route('school-years.index')->with('success', 'School year updated successfully.');
    }

    public function destroy(SchoolYear $schoolYear)
    {
        $schoolYear->delete();
        return redirect()->route('school-years.index')->with('success', 'School year archived.');
    }

    public function restore($id)
    {
        $schoolYear = SchoolYear::withTrashed()->findOrFail($id);
        $schoolYear->restore();

        return redirect()->route('school-years.index')->with('success', 'School year restored.');
    }

    private function validateSchoolYear(Request $request)
    {
        return $request->validate([
            'school_year_range' => [
                'required',
                'regex:/^\d{4}-\d{4}$/',
                function ($attribute, $value, $fail) {
                    [$start, $end] = explode('-', $value);
                    if ((int)$start >= (int)$end) {
                        $fail('Start year must be less than end year.');
                    }
                }
            ],
            'term' => 'required|integer|min:1|max:5',
        ]);
    }
}
