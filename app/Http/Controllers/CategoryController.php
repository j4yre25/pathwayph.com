<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;


class CategoryController extends Controller
{
   
    public function index() {
        $sectors = Sector::all(); // Fetch all sectors
        $categories = Category::all(); // Fetch all categories
        
    
        return Inertia::render('Categories/Index', [
            'sectors' => $sectors,
            'categories' => $categories,
        ]);
    
    }    

    public function list(Request $request) {
        $status = $request->input('status', 'all'); // Get the filter value for status (default to 'all')
        $sectorId = $request->input('sector'); // Get the filter value for sector
    
        // Query categories with filtering based on the status and sector
        $categories = Category::with('user')
            ->withTrashed() // Include archived (soft-deleted) records
            ->when($status === 'active', function ($query) {
                $query->whereNull('deleted_at'); // Active categories (not archived)
            })
            ->when($status === 'inactive', function ($query) {
                $query->whereNotNull('deleted_at'); // Inactive categories (archived)
            })
            ->when($sectorId, function ($query, $sectorId) {
                $query->where('sector_id', $sectorId); // Filter by sector
            })
            ->get();
    
        $sectors = Sector::all(); // Fetch all sectors for the filter dropdown
    
        return Inertia::render('Categories/List', [
            'categories' => $categories,
            'status' => $status, // Pass the current filter status to the view
            'sectors' => $sectors, // Pass the sectors to the view
            'selectedSector' => $sectorId, // Pass the selected sector to the view
        ]);
    }

    public function archivedlist()
    {
        // Fetch archived categories with their related sector and user
        $all_categories = Category::onlyTrashed()
            ->with(['sector', 'user']) // Eager load both sector and user relationships
            ->get();
    
        // Map the sector name and user name to each category
        $all_categories->transform(function ($category) {
            $category->sectorName = $category->sector->name ?? 'Unknown';
            $category->userName = $category->user->peso_first_name ?? 'Unknown'; // Add user name
            return $category;
        });
    
        return Inertia::render('Categories/ArchivedList', [
            'all_categories' => $all_categories
        ]);
    }
    
    public function create(Sector $sector) {
        $sectors = Sector::all();
        return Inertia::render('Categories/CreateCategories', [
            'sectors' => $sectors, 
            'sector' => $sector,
            

        ]);
    }

    public function store(Request $request, Sector $sector) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories'],
        ]);

        $new_category = new Category();
        $new_category->user_id = $request->user()->id; 
        $new_category->name = $validated['name'];
        $new_category->sector_id = $sector->id; 
        $new_category->save();

        return redirect()->back()->with('flash.banner', 'Category added successfully.');
    }


   



    public function edit(Category $category) {
        return Inertia::render('Categories/Edit/Index', [
            'category' => $category
        ]);
    }

    public function update(Request $request, Category $category) {
        Gate::authorize('update', $category);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        
        $category->name = $validated['name'];
        $category->save();

        return redirect()->back()->with('flash.banner', 'Category updated successfully.');
    }

    public function delete(Request $request, Category $category) {
        $category->delete();

        return redirect()->route('categories.index', ['sector' => $category->sector_id])->with('flash.banner', 'Category deleted successfully.');
    }

    public function restore($category) {
        $category = Category::withTrashed()->findOrFail($category);

        $category->restore();

        return redirect()->back()->with('flash.banner', 'Category restored successfully.');
    }

}



