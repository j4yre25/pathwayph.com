<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\HumanResource;
use Illuminate\Http\Request;

class CompanyDepartmentController extends Controller
{
    public function index()
    {
        $hr = HumanResource::where('user_id', auth()->id())->first();
        $departments = Department::where('company_id', $hr->company_id)
            ->with('hr') // Make sure you have this relationship in Department model
            ->get()
            ->map(function ($dep) {
                $dep->hr_name = $dep->hr ? $dep->hr->first_name . ' ' . $dep->hr->last_name : '';
                return $dep;
        });
        return inertia('Company/ManageHR/Index/Department/Index', [
            'departments' => $departments,
            'hr' => $hr,
            ]);
    }

    public function store(Request $request)
    {
        $hr = HumanResource::where('user_id', auth()->id())->first();
        $request->validate(['department_name' => 'required|string|max:255']);
        Department::create([
            'human_resources_id' => $hr->id,
            'company_id' => $hr->company_id,
            'department_name' => $request->department_name,
        ]);
        return redirect()->back()->with('success', 'Department added!');
    }

    public function batchStore(Request $request)
    {
        $hr = HumanResource::where('user_id', auth()->id())->first();
        $request->validate(['department_names' => 'required|array']);
        foreach ($request->department_names as $name) {
            Department::create([
                'human_resources_id' => $hr->id,
                'company_id' => $hr->company_id,
                'department_name' => $name,
            ]);
        }
        return redirect()->back()->with('success', 'Departments added!');
    }

    public function manage(Department $department)
    {
        $hr = HumanResource::where('user_id', auth()->id())->first();
        $departments = Department::where('company_id', $hr->company_id)
            ->with('hr') // Make sure you have this relationship in Department model
            ->get()
            ->map(function ($dep) {
                $dep->hr_name = $dep->hr ? $dep->hr->first_name . ' ' . $dep->hr->last_name : '';
                return $dep;
        });
        return inertia('Company/ManageHR/Index/Department/ManageDepartment', [
            'departments' => $departments,
            'hr' => $hr,
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $request->validate(['department_name' => 'required|string|max:255']);
        $department->update(['department_name' => $request->department_name]);
        return redirect()->back()->with('success', 'Department updated!');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->back()->with('success', 'Department deleted!');
    }
}