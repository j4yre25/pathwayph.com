<?php

namespace App\Http\Controllers;

use App\Models\Degree;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DegreeController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('status', 'active');

        $query = Degree::query();
        if ($filter === 'inactive') {
            $query->onlyTrashed();
        }

        $degrees = $query->orderBy('name')->get();

        return Inertia::render('Institutions/Degrees', [
            'degrees' => $degrees,
            'filter' => $filter,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:degrees,name',
            'type' => 'required|in:Bachelor,Associate,Master,Doctoral,Diploma',
        ]);

        Degree::create($validated);

        return redirect()->route('degrees.index')->with('success', 'Degree added successfully.');
    }

    public function update(Request $request, Degree $degree)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:degrees,name,' . $degree->id,
            'type' => 'required|in:Bachelor,Associate,Master,Doctoral,Diploma',
        ]);

        $degree->update($validated);

        return redirect()->route('degrees.index')->with('success', 'Degree updated successfully.');
    }

    public function destroy(Degree $degree)
    {
        $degree->delete();

        return redirect()->route('degrees.index')->with('success', 'Degree archived successfully.');
    }

    public function restore($id)
    {
        $degree = Degree::withTrashed()->findOrFail($id);
        $degree->restore();

        return redirect()->route('degrees.index')->with('success', 'Degree restored successfully.');
    }
}
