<?php

namespace App\Http\Controllers;

use App\Models\Degree;
use App\Models\InstitutionDegree;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DegreeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();
        $degrees = InstitutionDegree::with('degree')
            ->where('institution_id', $institution?->id)
            ->get();

        return Inertia::render('Institutions/Degrees/Index', [
            'degrees' => $degrees,
        ]);
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();
        $status = $request->input('status', 'all');

        $query = InstitutionDegree::with('degree')
            ->withTrashed()
            ->where('institution_id', $institution?->id);

        if ($status === 'active') {
            $query->whereNull('deleted_at');
        } elseif ($status === 'inactive') {
            $query->whereNotNull('deleted_at');
        }

        $degrees = $query->get();

        return Inertia::render('Institutions/Degrees/List', [
            'degrees' => $degrees,
            'status' => $status,
        ]);
    }

    public function archivedList()
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();
        $all_degrees = InstitutionDegree::with('degree')
            ->onlyTrashed()
            ->where('institution_id', $institution?->id)
            ->get();

        return Inertia::render('Institutions/Degrees/ArchivedList', [
            'all_degrees' => $all_degrees
        ]);
    }

    public function create()
    {
        return Inertia::render('Institutions/Degrees/Create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();
        if (!$institution) {
            return back()->withErrors(['institution' => 'Institution not found for this user.']);
        }

        $request->validate([
            'type' => ['required', 'in:Bachelor,Associate,Master,Doctoral,Diploma'],
        ]);

        // Check if degree exists globally
        $degree = Degree::withTrashed()
            ->where('type', $request->type)
            ->first();

        if (!$degree) {
            $degree = Degree::create([
                'type' => $request->type,
            ]);
        }

        // Check if this institution already has this degree
        $exists = InstitutionDegree::withTrashed()
            ->where('institution_id', $institution->id)
            ->where('degree_id', $degree->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['flash.banner' => 'This degree already exists for your institution.']);
        }

        InstitutionDegree::create([
            'degree_id' => $degree->id,
            'institution_id' => $institution->id,
        ]);

        return redirect()->back()->with('flash.banner', 'Degree added.');
    }

    public function edit($id)
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();
        $degree = InstitutionDegree::with('degree')
            ->where('institution_id', $institution?->id)
            ->findOrFail($id);

        return Inertia::render('Institutions/Degrees/Edit', [
            'degree' => $degree
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();

        $request->validate([
            'type' => ['required', 'in:Bachelor,Associate,Master,Doctoral,Diploma'],
        ]);

        $degree = Degree::withTrashed()
            ->where('type', $request->type)
            ->first();

        if (!$degree) {
            $degree = Degree::create([
                'type' => $request->type,
            ]);
        }

        $exists = InstitutionDegree::withTrashed()
            ->where('institution_id', $institution->id)
            ->where('degree_id', $degree->id)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['flash.banner' => 'This degree already exists for your institution.']);
        }

        $institutionDegree = InstitutionDegree::where('institution_id', $institution->id)->findOrFail($id);

        $institutionDegree->update([
            'degree_id' => $degree->id,
        ]);

        return redirect()->back()->with('flash.banner', 'Degree updated.');
    }

    public function delete(Request $request, $id)
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();
        $degree = InstitutionDegree::where('institution_id', $institution?->id)->findOrFail($id);
        $degree->delete();

        return back()->with('flash.banner', 'Degree archived.');
    }

    public function restore($id)
    {
        $user = Auth::user();
        $institution = Institution::where('institution_id', $user->id)->first();
        $degree = InstitutionDegree::withTrashed()
            ->where('institution_id', $institution?->id)
            ->findOrFail($id);
        $degree->restore();

        return redirect()->back()->with('flash.banner', 'Degree restored.');
    }
}
