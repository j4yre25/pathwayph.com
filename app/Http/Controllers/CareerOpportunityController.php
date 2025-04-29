<?php

namespace App\Http\Controllers;

use App\Models\CareerOpportunity;
use App\Models\Program;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class CareerOpportunityController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('status', 'active');

        $query = CareerOpportunity::with('programs');

        if ($filter === 'inactive') {
            $query->onlyTrashed();
        }

        return Inertia::render('Institutions/CareerOpportunities', [
            'careerOpportunities' => $query->get()->map(function ($co) {
                return [
                    'id' => $co->id,
                    'title' => $co->title,
                    'programs' => $co->programs->pluck('name'),
                    'program_ids' => $co->programs->pluck('id'),
                ];
            }),
            'programs' => Program::all(),
            'filter' => $filter,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'program_ids' => 'required|array|min:1',
            'program_ids.*' => 'exists:programs,id',
        ]);

        // Split multiple titles from comma input
        $titles = array_map('trim', explode(',', $validated['title']));

        foreach ($titles as $title) {
            // Check if title already exists (case-insensitive)
            $existing = CareerOpportunity::whereRaw('LOWER(title) = ?', [strtolower($title)])->first();

            if ($existing) {
                // Attach new programs (avoids duplicates)
                $existing->programs()->syncWithoutDetaching($validated['program_ids']);
            } else {
                // Create and attach programs
                $opportunity = CareerOpportunity::create(['title' => $title]);
                $opportunity->programs()->attach($validated['program_ids']);
            }
        }

        return redirect()->route('career-opportunities.index')->with('success', 'Career opportunities saved.');
    }

    public function update(Request $request, CareerOpportunity $careerOpportunity)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'program_ids' => 'required|array|min:1',
            'program_ids.*' => 'exists:programs,id',
        ]);

        // Ensure no duplicate title exists in other record
        $duplicate = CareerOpportunity::whereRaw('LOWER(title) = ?', [strtolower($validated['title'])])
            ->where('id', '!=', $careerOpportunity->id)
            ->first();

        if ($duplicate) {
            return back()->withErrors(['msg' => 'A career opportunity with this title already exists.']);
        }

        $careerOpportunity->update(['title' => $validated['title']]);
        $careerOpportunity->programs()->sync($validated['program_ids']);

        return redirect()->route('career-opportunities.index')->with('success', 'Career opportunity updated.');
    }

    public function destroy(CareerOpportunity $careerOpportunity)
    {
        $careerOpportunity->delete();

        return redirect()->route('career-opportunities.index')->with('success', 'Career opportunity archived.');
    }

    public function restore($id)
    {
        $opportunity = CareerOpportunity::withTrashed()->findOrFail($id);
        $opportunity->restore();

        return redirect()->route('career-opportunities.index')->with('success', 'Career opportunity restored.');
    }
}
