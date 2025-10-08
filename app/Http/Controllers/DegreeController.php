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
        $institution = Institution::where('user_id', $user->id)->first();
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
        $institution = Institution::where('user_id', $user->id)->first();
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
        $institution = Institution::where('user_id', $user->id)->first();
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
        $institution = Institution::where('user_id', $user->id)->first();
        if (!$institution) {
            return back()->withErrors(['institution' => 'Institution not found for this user.']);
        }

        $request->validate([
            'type' => ['required', 'in:Bachelor\'s Degree,Master\'s Degree,Doctoral Degree,Associate Degree,Diploma'],
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

        // Generate degree code
        $initials = $this->getDegreeInitials($request->type);

        // Find the latest code for this type and institution
        $lastDegree = InstitutionDegree::where('institution_id', $institution->id)
            ->whereHas('degree', function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->orderByDesc('id')
            ->first();

        if ($lastDegree && $lastDegree->degree_code) {
            // Extract the number and increment
            $parts = explode('-', $lastDegree->degree_code);
            $number = isset($parts[1]) ? intval($parts[1]) + 1 : 1;
        } else {
            // Start at 1 for new type/institution
            $number = 1;
        }

        // You can set the starting number for each type if you want (e.g., BA starts at 1000)
        $startNumbers = [
            'BD' => 1,
            'MD' => 1,
            'PhD' => 1,
            'AD' => 1,
            'DP' => 1,
        ];
        if (!$lastDegree) {
            $number = $startNumbers[$initials] ?? 1;
        }

        $degreeCode = $initials . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);

        InstitutionDegree::create([
            'degree_id' => $degree->id,
            'institution_id' => $institution->id,
            'degree_code' => $degreeCode,
        ]);

        return redirect()->back()->with('flash.banner', 'Degree added.');
    }

    public function edit($id)
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->first();
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
        $institution = Institution::where('user_id', $user->id)->first();

        $request->validate([
            'type' => [
                'required',
                'in:Bachelor of Science,Bachelor of Arts,Associate,Master of Science,
                                    Master of Arts,Doctoral,Diploma'
            ],
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
        $institution = Institution::where('user_id', $user->id)->first();
        $degree = InstitutionDegree::where('institution_id', $institution?->id)->findOrFail($id);
        $degree->delete();

        return back()->with('flash.banner', 'Degree archived.');
    }

    public function restore($id)
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->first();
        $degree = InstitutionDegree::withTrashed()
            ->where('institution_id', $institution?->id)
            ->findOrFail($id);
        $degree->restore();

        return redirect()->back()->with('flash.banner', 'Degree restored.');
    }

    private function getDegreeInitials($type)
    {
        return [
            "Bachelor's Degree" => 'BD',
            "Master's Degree" => 'MD',
            "Doctoral Degree" => 'PhD',
            "Associate Degree" => 'AD',
            "Diploma" => 'DP',
        ][$type] ?? 'XX';
    }
}
