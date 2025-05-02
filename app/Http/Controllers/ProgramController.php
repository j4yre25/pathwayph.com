<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Degree;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProgramController extends Controller
{
    public function index(User $user)
    {
        $programs = $user->programs()->with('degree')->withTrashed()->get();
        $degrees = $user->degrees;

        return Inertia::render('Institutions/Programs/Index', [
            'programs' => $programs,
            'degrees' => $degrees,
        ]);
    }

    public function list(Request $request)
    {
        $status = $request->input('status', 'all');

        $programs = Program::with(['user', 'degree'])->withTrashed()
            ->when($status === 'active', fn($query) => $query->whereNull('deleted_at'))
            ->when($status === 'inactive', fn($query) => $query->whereNotNull('deleted_at'))
            ->get();

        return Inertia::render('Institutions/Programs/List', [
            'programs' => $programs,
            'status' => $status,
        ]);
    }

    public function archivedList()
    {
        $all_programs = Program::with(['user', 'degree'])->onlyTrashed()->get();

        return Inertia::render('Institutions/Programs/ArchivedList', [
            'all_programs' => $all_programs,
        ]);
    }

    public function create(User $user)
    {
        $degrees = $user->degrees()->get();

        return Inertia::render('Institutions/Programs/Create', [
            'degrees' => $degrees,
        ]);
    }

    public function store(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'degree_id' => ['required', 'exists:degrees,id'],
        ]);

        $exists = Program::withTrashed()
            ->where('user_id', $user->id)
            ->where('name', $request->name)
            ->where('degree_id', $request->degree_id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['flash.banner' => 'This program already exists.']);
        }

        Program::create([
            'name' => $request->name,
            'degree_id' => $request->degree_id,
            'user_id' => $user->id,
        ]);

        return redirect()->back()->with('flash.banner', 'Program added.');
    }

    public function edit(Program $program)
    {
        

        $degrees = Degree::where('user_id', $program->user_id)->get();

        return Inertia::render('Institutions/Programs/Edit', [
            'program' => $program,
            'degrees' => $degrees,
        ]);
    }

    public function update(Request $request, Program $program)
    {
        

        $request->validate([
            'name' => ['required', 'string'],
            'degree_id' => ['required', 'exists:degrees,id'],
        ]);

        $exists = Program::withTrashed()
            ->where('user_id', $program->user_id)
            ->where('name', $request->name)
            ->where('degree_id', $request->degree_id)
            ->where('id', '!=', $program->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['flash.banner' => 'Duplicate program entry.']);
        }

        $program->update([
            'name' => $request->name,
            'degree_id' => $request->degree_id,
        ]);

        return redirect()->back()->with('flash.banner', 'Program updated.');
    }

    public function delete(Request $request, Program $program)
    {
        $program->delete();

        return redirect()->route('programs.index', ['user' => $request->user()->id])
            ->with('flash.banner', 'Program archived.');
    }

    public function restore($id)
    {
        $program = Program::withTrashed()->findOrFail($id);
        $program->restore();

        return redirect()->back()->with('flash.banner', 'Program restored.');
    }
}
