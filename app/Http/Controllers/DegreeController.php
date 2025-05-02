<?php

namespace App\Http\Controllers;

use App\Models\Degree;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DegreeController extends Controller
{
    public function index(User $user)
    {
        $degrees = $user->degrees;

        return Inertia::render('Institutions/Degrees/Index', [
            'degrees' => $degrees
        ]);
    }

    public function list(Request $request)
    {
        $status = $request->input('status', 'all');

        $degrees = Degree::with('user')->withTrashed()
        ->when($status === 'active', fn($query) => $query->whereNull('deleted_at'))
        ->when($status === 'inactive', fn($query) => $query->whereNotNull('deleted_at'))
        ->get();

        return Inertia::render('Institutions/Degrees/List', [
            'degrees' => $degrees,
            'status' => $status,
        ]);
    }

    public function archivedList()
    {
        $all_degrees = Degree::with('user')->onlyTrashed()->get();

        return Inertia::render('Institutions/Degrees/ArchivedList', [
            'all_degrees' => $all_degrees
        ]);
    }

    public function create(User $user)
    {
        return Inertia::render('Institutions/Degrees/Create');
    }

    public function store(Request $request, User $user)
    {
        $request->validate([
            'type' => ['required', 'in:Bachelor,Associate,Master,Doctoral,Diploma'],
        ]);

        $exists = Degree::withTrashed()
            ->where('user_id', $user->id)
            ->where('type', $request->type)
            ->exists();

        if ($exists) {
            return back()->withErrors(['flash.banner' => 'This degree type already exists.']);
        }

        Degree::create([
            'type' => $request->type,
            'user_id' => $user->id,
        ]);

        return redirect()->back()->with('flash.banner', 'Degree added.');
    }

    public function edit(Degree $degree)
    {
        return Inertia::render('Institutions/Degrees/Edit', [
            'degree' => $degree
        ]);
    }

    public function update(Request $request, Degree $degree)
    {

        $request->validate([
            'type' => ['required', 'in:Bachelor,Associate,Master,Doctoral,Diploma'],
        ]);

        $exists = Degree::withTrashed()
            ->where('user_id', $degree->user_id)
            ->where('type', $request->type)
            ->where('id', '!=', $degree->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['flash.banner' => 'This degree type already exists.']);
        }

        $degree->update([
            'type' => $request->type
        ]);

        return redirect()->back()->with('flash.banner', 'Degree updated.');
    }

    public function delete(Request $request, Degree $degree)
    {
        $degree->delete();

    return redirect()->route('degrees', ['user' => $degree->user_id])
        ->with('flash.banner', 'Degree archived.');
    }

    public function restore($id)
    {
        $degree = Degree::withTrashed()->findOrFail($id);
        $degree->restore();

        return redirect()->back()->with('flash.banner', 'Degree restored.');
    }
}
