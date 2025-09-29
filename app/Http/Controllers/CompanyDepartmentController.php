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
    

    public function batchUpload(Request $request)
    {
        $hr = HumanResource::where('user_id', auth()->id())->first();
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        $file = $request->file('file');
        $departments = [];

        // Use PhpSpreadsheet or Laravel Excel for .xlsx, or native for .csv
        if ($file->getClientOriginalExtension() === 'csv') {
            $rows = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_map('trim', $rows[0]);
            foreach (array_slice($rows, 1) as $row) {
                $data = array_combine($header, $row);
                if (!empty($data['department_name'])) {
                    $departments[] = trim($data['department_name']);
                }
            }
        } else {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            $header = array_map('trim', $rows[0]);
            foreach (array_slice($rows, 1) as $row) {
                $data = array_combine($header, $row);
                if (!empty($data['department_name'])) {
                    $departments[] = trim($data['department_name']);
                }
            }
        }

        // Remove duplicates (both in file and already existing in DB)
        $departments = array_unique($departments);
        $existing = Department::where('company_id', $hr->company_id)
            ->whereIn('department_name', $departments)
            ->pluck('department_name')
            ->toArray();
        $toInsert = array_diff($departments, $existing);

        foreach ($toInsert as $name) {
            Department::create([
                'human_resources_id' => $hr->id,
                'company_id' => $hr->company_id,
                'department_name' => $name,
            ]);
        }

        return redirect()->route('company.departments.index')->with('success', 'Departments batch uploaded!');
    }

    public function downloadTemplate()
    {
        return response()->download(public_path('templates/department_template.csv'));
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

    public function archived()
    {
        $hr = HumanResource::where('user_id', auth()->id())->first();
        $archivedDepartments = Department::onlyTrashed()
            ->where('company_id', $hr->company_id)
            ->with('hr')
            ->get()
            ->map(function ($dep) {
                $dep->hr_name = $dep->hr ? $dep->hr->first_name . ' ' . $dep->hr->last_name : '';
                return $dep;
            });
        
        return inertia('Company/ManageHR/Index/Department/ArchivedDept', [
            'archivedDepartments' => $archivedDepartments,
            'hr' => $hr,
        ]);
    }

    public function restore($id)
    {
        $department = Department::onlyTrashed()->findOrFail($id);
        $department->restore();
        return redirect()->back()->with('success', 'Department restored successfully!');
    }
}