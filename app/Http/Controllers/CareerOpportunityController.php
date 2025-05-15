<?php

namespace App\Http\Controllers;

use App\Models\CareerOpportunity;
use App\Models\InstitutionCareerOpportunity;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CareerOpportunityController extends Controller
{
    public function index(User $user, Request $request)
    {
        $programs = $user->programs;
        $filterProgramId = $request->input('program_id');

        $query = InstitutionCareerOpportunity::with(['careerOpportunity', 'program'])
            ->where('institution_id', $user->id);

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
        $status = $request->input('status', 'all');
        $filterProgramId = $request->input('program_id');

        $query = InstitutionCareerOpportunity::with(['careerOpportunity', 'program']) // Eager loading
            ->withTrashed()
            ->where('institution_id', $user->id);

        if ($filterProgramId) {
            $query->where('program_id', $filterProgramId);
        }

        if ($status === 'active') {
            $query->whereNull('deleted_at');
        } elseif ($status === 'inactive') {
            $query->whereNotNull('deleted_at');
        }

        $opportunities = $query->get();
        $programs = $user->programs;

        return Inertia::render('Institutions/CareerOpportunities/List', [
            'opportunities' => $opportunities,
            'programs' => $programs,
            'status' => $status,
        ]);
    }

    public function archivedList(User $user)
    {
        $archived = InstitutionCareerOpportunity::with(['careerOpportunity', 'program']) // Eager loading
            ->onlyTrashed()
            ->where('institution_id', $user->id)
            ->get();

        return Inertia::render('Institutions/CareerOpportunities/ArchivedList', [
            'opportunities' => $archived,
        ]);
    }

    public function create(User $user)
    {
        // Fetch only active programs (not archived)
        $programs = $user->programs()->whereNull('deleted_at')->get();

        return Inertia::render('Institutions/CareerOpportunities/Create', [
            'programs' => $programs,
        ]);
    }

    public function store(Request $request, User $user)
    {
        logger()->info('Received form data:', $request->all());

        $request->validate([
            'program_ids' => 'required|array|min:1',
            'program_ids.*' => 'required|integer|exists:programs,id',
            'titles' => 'required|string',
        ]);

        // 🔒 Filter only programs that belong to this institution
        $validProgramIds = Program::whereIn('id', $request->program_ids)
            ->where('institution_id', $user->id)
            ->pluck('id')
            ->toArray();

        if (count($validProgramIds) !== count($request->program_ids)) {
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

            foreach ($validProgramIds as $programId) {
                // Check for duplicates
                $duplicate = InstitutionCareerOpportunity::where([
                    'career_opportunity_id' => $existing->id,
                    'program_id' => $programId,
                    'institution_id' => $user->id,
                ])->exists();

                if (!$duplicate) {
                    InstitutionCareerOpportunity::create([
                        'career_opportunity_id' => $existing->id,
                        'program_id' => $programId,
                        'institution_id' => $user->id,
                    ]);
                    $created[] = $existing->title;
                } else {
                    $duplicates[] = $existing->title;
                }
            }
        }

        $message = '';
        if (!empty($created)) {
            $message .= 'Career opportunities added: ' . implode(', ', $created) . '. ';
        }
        if (!empty($duplicates)) {
            $message .= 'The following entries already exist: ' . implode(', ', $duplicates) . '.';
        }

        return back()->with('flash.banner', $message);
    }

    public function edit($id)
    {
        $link = InstitutionCareerOpportunity::with(['careerOpportunity', 'program'])->findOrFail($id);

        return Inertia::render('Institutions/CareerOpportunities/Edit', [
            'link' => $link,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => ['required', 'string'],
        ]);

        // Find the InstitutionCareerOpportunity entry
        $link = InstitutionCareerOpportunity::with('careerOpportunity')->findOrFail($id);

        // Check if the title already exists in the career_opportunities table
        $existingCareerOpportunity = CareerOpportunity::whereRaw('LOWER(title) = ?', [Str::lower($request->title)])->first();

        if (!$existingCareerOpportunity) {
            // If the title does not exist, create a new entry in career_opportunities
            $existingCareerOpportunity = CareerOpportunity::create(['title' => $request->title]);
        }

        // Update the institution_career_opportunities table to reference the new or existing career_opportunity
        $link->update(['career_opportunity_id' => $existingCareerOpportunity->id]);

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
