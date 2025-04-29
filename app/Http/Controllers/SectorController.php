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

    public function index(User $user) {


        $sectors = $user->sectors;
        return Inertia::render('Sectors/Index', [
            'sectors' => $sectors
        ]);

        
    }
    public function list(Request $request) {


        $status = $request->input('status', 'all');

        // Query sectors with filtering based on the status
        $sectors = Sector::with('user')->withTrashed()
            ->when($status === 'active', function ($query) {
                $query->whereNull('deleted_at'); // Active sectors (not archived)
            })
            ->when($status === 'inactive', function ($query) {
                $query->whereNotNull('deleted_at'); // Inactive sectors (archived)
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
    
    public function create(User $user) {

        return Inertia::render('Sectors/CreateSectors');



    }

    public function store(Request $request, User $user) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:sectors'],
        ]);
        // return Inertia::render('Jobs/Index/CreateJobs');
        $new_sector = new Sector();
        $new_sector->user_id = $user->id;
        $new_sector->name = $request->input('name');
        $new_sector->save();

        return Redirect()->back()->with('flash.banner', 'Sector added successfully.');

}

   



    public function edit(Sector $sector) {
        return Inertia::render('Sectors/Edit/Index', [
            'sector' => $sector
        ]);
    }

    public function update(Request $request, Sector $sector){
        Gate::authorize('update', $sector);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:99'],


        ]);
        
        $sector->name = $request->name;
        $sector->save();

        return Redirect()->back()->with('flash.banner', 'Sector updated successfully.');
    }

    public function delete(Request $request, Sector $sector) {


        $sector->delete();
    
        $user_id = $request->user()->id;

     
        return Redirect(route('sectors', ['user' => $user_id]))->with('flash.banner', 'Sector archived successfully.');
        
    }

    public function restore($sector)
    {
        $sector = Sector::withTrashed()->findOrFail($sector);

        $sector->restore();

        return redirect()->back()->with('flash.banner', 'Sector restored successfully.');
    }
    
}
