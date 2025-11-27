<?php

namespace App\Http\Controllers;

use App\Models\CareerOpportunity;
use App\Models\Institution;
use App\Models\InstitutionCareerOpportunity;
use App\Models\Program;
use App\Models\InstitutionProgram;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CareerOpportunityController extends Controller
{
    public function index(User $user, Request $request)
    {
        // Get the institution for this user
        $institution = Institution::where('user_id', $user->id)->first();
        if (!$institution) {
            return Inertia::render('Institutions/CareerOpportunities/Index', [
                'programs' => [],
                'opportunities' => [],
            ]);
        }

        $programs = $institution->institutionPrograms()
            ->whereNull('deleted_at')
            ->with('program')
            ->get();

        $filterProgramId = $request->input('program_id');

        $query = InstitutionCareerOpportunity::with([
            'careerOpportunity',
            'program'
        ])
            ->where('institution_id', $institution->id);

        if ($filterProgramId) {
            $query->where('program_id', $filterProgramId);
        }

        $opportunities = $query->get();

        return Inertia::render('Institutions/CareerOpportunities/Index', [
            'programs' => $programs,
            'opportunities' => $opportunities,
        ]);
    }

    public function list(Request $request, User $user)
    {
        $institution = Institution::where('user_id', $user->id)->first();
        if (!$institution) {
            return Inertia::render('Institutions/CareerOpportunities/List', [
                'opportunities' => [],
                'programs' => [],
                'status' => $request->input('status', 'all'),
            ]);
        }

        $status = $request->input('status', 'all');
        $filterProgramId = $request->input('program_id');

        $query = InstitutionCareerOpportunity::with(['careerOpportunity', 'program'])
            ->withTrashed()
            ->where('institution_id', $institution->id);

        if ($filterProgramId) {
            $query->where('program_id', $filterProgramId);
        }

        if ($status === 'active') {
            $query->whereNull('deleted_at');
        } elseif ($status === 'inactive') {
            $query->whereNotNull('deleted_at');
        }

        $opportunities = $query->get();

        $programs = $institution->institutionPrograms()
            ->whereNull('deleted_at')
            ->with('program')
            ->get();

        return Inertia::render('Institutions/CareerOpportunities/List', [
            'opportunities' => $opportunities,
            'programs' => $programs,
            'status' => $status,
        ]);
    }

    public function archivedList(User $user)
    {
        $institution = Institution::where('user_id', $user->id)->first();
        if (!$institution) {
            return Inertia::render('Institutions/CareerOpportunities/ArchivedList', [
                'opportunities' => [],
            ]);
        }

        $archived = InstitutionCareerOpportunity::with(['careerOpportunity', 'program'])
            ->onlyTrashed()
            ->where('institution_id', $institution->id)
            ->get();

        return Inertia::render('Institutions/CareerOpportunities/ArchivedList', [
            'opportunities' => $archived,
        ]);
    }

    public function create(User $user)
    {
        $institution = Institution::where('user_id', $user->id)->first();
        $programs = [];
        if ($institution) {
            $programs = $institution->institutionPrograms()
                ->whereNull('deleted_at')
                ->with('program')
                ->get();
        }

        return Inertia::render('Institutions/CareerOpportunities/Create', [
            'programs' => $programs,
        ]);
    }

    public function store(Request $request, User $user)
    {
        $request->validate([
            'program_ids' => 'required|array|min:1',
            'program_ids.*' => 'required|integer|exists:institution_programs,id',
            'titles' => 'required|string',
        ]);

        $institution = Institution::where('user_id', $user->id)->first();
        if (!$institution) {
            return back()->withErrors(['flash.banner' => 'Institution not found.']);
        }

        $institutionPrograms = InstitutionProgram::whereIn('id', $request->program_ids)
            ->where('institution_id', $institution->id)
            ->get();

        if (count($institutionPrograms) !== count($request->program_ids)) {
            return back()->withErrors([
                'flash.banner' => 'Invalid program selection. Please only use your own programs.',
            ]);
        }

        // Split and clean the titles
        $titles = array_filter(array_map('trim', explode(',', $request->titles)));
        $created = [];
        $duplicates = [];

        foreach ($titles as $title) {
            $lowerTitle = Str::lower($title);
            $existing = CareerOpportunity::withTrashed()
                ->whereRaw('LOWER(title) = ?', [$lowerTitle])
                ->first();

            if (!$existing) {
                $existing = CareerOpportunity::create(['title' => $title]);
            }

            foreach ($institutionPrograms as $instProg) {
                $duplicate = InstitutionCareerOpportunity::where([
                    'career_opportunity_id' => $existing->id,
                    'program_id' => $instProg->program_id,
                    'institution_id' => $instProg->institution_id,
                ])->exists();

                if (!$duplicate) {
                    InstitutionCareerOpportunity::create([
                        'career_opportunity_id' => $existing->id,
                        'program_id' => $instProg->program_id,
                        'institution_id' => $instProg->institution_id,
                    ]);
                    $created[] = $existing->title;
                } else {
                    $duplicates[] = $existing->title;
                }
            }
        }

        $message = '';
        if (!empty($created)) {
            if (count($created) > 1) {
                $message .= 'Career opportunities are being added for all of the programs selected. ';
            } else {
                $message .= 'Career opportunity added: ' . implode(', ', $created) . '. ';
            }
        }
        if (!empty($duplicates)) {
            $message .= 'The following entries already exist: ' . implode(', ', $duplicates) . '.';
        }

        return back()->with('flash.banner', $message);
    }

    public function edit($id)
    {
        $link = InstitutionCareerOpportunity::with(['careerOpportunity'])->findOrFail($id);

        // Get the institution from the link
        $institutionId = $link->institution_id;
        $institution = Institution::find($institutionId);

        // Get all programs for this institution
        $programs = [];
        if ($institution) {
            $programs = $institution->institutionPrograms()
                ->whereNull('deleted_at')
                ->with('program')
                ->get();
        }

        return Inertia::render('Institutions/CareerOpportunities/Edit', [
            'link' => $link,
            'programs' => $programs,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => ['required', 'string'],
            'program_id' => ['required', 'integer', 'exists:institution_programs,id'],
        ]);

        $link = InstitutionCareerOpportunity::with('careerOpportunity')->findOrFail($id);

        // Update or create the career opportunity title
        $existingCareerOpportunity = CareerOpportunity::whereRaw('LOWER(title) = ?', [Str::lower($request->title)])->first();
        if (!$existingCareerOpportunity) {
            $existingCareerOpportunity = CareerOpportunity::create(['title' => $request->title]);
        }

        // Get the selected institution program
        $institutionProgram = InstitutionProgram::findOrFail($request->program_id);

        // Update the link
        $link->update([
            'career_opportunity_id' => $existingCareerOpportunity->id,
            'program_id' => $institutionProgram->program_id,
            'institution_id' => $institutionProgram->institution_id,
        ]);

        return redirect()->back()->with('flash.banner', 'Career opportunity updated successfully.');
    }

    public function delete(Request $request, $id)
    {
        $link = InstitutionCareerOpportunity::findOrFail($id);
        $link->delete();

        return back()->with('flash.banner', 'Career opportunity archived.');
    }

    public function restore($id)
    {
        $link = InstitutionCareerOpportunity::withTrashed()->findOrFail($id);
        $link->restore();

        return back()->with('flash.banner', 'Career opportunity restored.');
    }
}
