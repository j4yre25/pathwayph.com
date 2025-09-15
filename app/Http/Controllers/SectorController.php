<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;


class SectorController extends Controller
{

    public function index(User $user)
    {


        $sectors = Sector::all();
        return Inertia::render('Sectors/Index', [
            'sectors' => $sectors
        ]);
    }
    public function list(Request $request)
    {


        $status = $request->input('status', 'all');

        // Query sectors with filtering based on the status
        $sectors = Sector::with(['user', 'peso'])->withTrashed()
            ->when($status === 'active', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->when($status === 'inactive', function ($query) {
                $query->whereNotNull('deleted_at');
            })
            ->get();



        return Inertia::render('Sectors/List', [
            'sectors' => $sectors,
            'status' => $status, // Pass the current filter status to the view
        ]);
    }

    public function archivedlist()
    {


        $all_sectors = Sector::with('user')->onlyTrashed()->get();

        return Inertia::render('Sectors/ArchivedList', [
            'all_sectors' => $all_sectors


        ]);
    }

    public function create(User $user)
    {

        return Inertia::render('Sectors/CreateSectors');
    }
    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:sectors'],
            'division' => ['required', 'string'],
        ]);

        $new_sector = new Sector();
        $new_sector->user_id = $user->id;
        $new_sector->name = $request->input('name');
        $new_sector->division_codes = $request->input('division');
        // Set sector_code based on division_codes mapping
        $new_sector->sector_code = $this->divisionToSectorCode($new_sector->division_codes);

        $new_sector->save();

        // Now use the ID to generate sector_id
        $new_sector->sector_id = 'S' . str_pad($new_sector->id, 4, '0', STR_PAD_LEFT);
        $new_sector->save();

        return redirect()->back()->with('flash.banner', 'Sector added successfully.');
    }




    public function edit(Sector $sector)
    {
        return Inertia::render('Sectors/Edit/Index', [
            'sector' => $sector
        ]);
    }

    public function update(Request $request, Sector $sector)
    {
        // Gate::authorize('update', $sector);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:99'],


        ]);

        $sector->name = $request->name;
        $sector->save();

        return Redirect()->back()->with('flash.banner', 'Sector updated successfully.');
    }

    public function delete(Request $request, Sector $sector)
    {


        $sector->delete();

        $user_id = $request->user()->id;


        return Redirect(route('sectors', ['user' => $user_id]))->with('flash.banner', 'Sector archived successfully.');
    }

    public function restore(Request $request, $sector)
    {
        $sector = Sector::withTrashed()->findOrFail($sector);

        $sector->restore();
        $user_id = $request->user()->id;


        return redirect()->route('sectors',  ['user' => $user_id])->with('flash.banner', 'Sector restored successfully.');
    }

    private function divisionToSectorCode($division)
    {
        $map = [
            "01-03" => "A",
            "05-09" => "B",
            "10-33" => "C",
            "35" => "D",
            "36-39" => "E",
            "41-43" => "F",
            "45-47" => "G",
            "49-53" => "H",
            "55-56" => "I",
            "58-63" => "J",
            "64-66" => "K",
            "68" => "L",
            "69-75" => "M",
            "77-82" => "N",
            "84" => "O",
            "85" => "P",
            "86-88" => "Q",
            "90-93" => "R",
            "94-96" => "S",
            "98-98" => "T",
            "99" => "U",
            "PESO" => "PESO",
        ];
        return $map[$division] ?? null;
    }
}
