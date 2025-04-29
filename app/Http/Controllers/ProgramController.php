<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Degree;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('status', 'active');

        $query = Program::with('degree');
        if ($filter === 'inactive') {
            $query->onlyTrashed();
        }

        $programs = $query->get()->map(function ($program) {
            return [
                'id' => $program->id,
                'name' => $program->name,
                'formatted_name' => $program->formatted_name,
                'degree' => $program->degree->name ?? null,
            ];
        });

        return Inertia::render('Institutions/Programs', [
            'programs' => $programs,
            'degrees' => Degree::all(),
            'filter' => $filter,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'degree_id' => 'required|exists:degrees,id',
        ]);

        // Avoid duplicate degree+name combo
        if (Program::where('name', $validated['name'])
            ->where('degree_id', $validated['degree_id'])
            ->exists()) {
            return back()->withErrors(['msg' => 'This program already exists under that degree.']);
        }

        Program::create($validated);

        return redirect()->route('programs.index')->with('success', 'Program added successfully.');
    }

    public function update(Request $request, Program $program)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'degree_id' => 'required|exists:degrees,id',
        ]);

        if (Program::where('name', $validated['name'])
            ->where('degree_id', $validated['degree_id'])
            ->where('id', '!=', $program->id)
            ->exists()) {
            return back()->withErrors(['msg' => 'Another program with the same name and degree exists.']);
        }

        $program->update($validated);

        return redirect()->route('programs.index')->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program)
    {
        $program->delete();

        return redirect()->route('programs.index')->with('success', 'Program archived successfully.');
    }

    public function restore($id)
    {
        $program = Program::withTrashed()->findOrFail($id);
        $program->restore();

        return redirect()->route('programs.index')->with('success', 'Program restored successfully.');
    }
}
