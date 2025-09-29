<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\HrInfoView;
use App\Models\Department;
use App\Models\HumanResource;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class CompanyHRDeptController extends Controller
{
    /**
     * Show the list of HRs under the company.
     *
     * @return \Inertia\Response
     */
    public function hrDept(Request $request)
    {
        /** @var \App\Models\User $user */
         $user = Auth::user();

        // Get the company_id linked to the currently logged-in HR
        $companyId = $user->hr->company_id;
        
        // Query your view and filter by company_id
        $hrs = DB::table('company_hr_view')
            ->where('company_id', $companyId)
            ->get();

        $departments = Department::where('company_id', $companyId)
            ->with('hr') 
            ->get()
            ->map(function ($dep) {
                $dep->hr_name = $dep->hr ? $dep->hr->first_name . ' ' . $dep->hr->last_name : '';
                return $dep;
        });

        
        return inertia('Company/ManageHR/Index/Index', [
            'hrs' => $hrs,
            'departments' => $departments,
        ]);
    }


    public function editHR($id)
    {
        $user = Auth::user();// Get the logged-in user (company)

        // Ensure only main HR can edit HRs
        if ($user->role !== 'company' || !$user->is_main_hr) {
            abort(403, 'Unauthorized access.');
        }

        // Fetch the HR to edit
        $hr = User::findOrFail($id);

        // Ensure the HR belongs to the same company
        if ($hr->company_email !== $user->company_email) {
            abort(403, 'Unauthorized access.');
        }

        return inertia('EditHR', [
            'hr' => $hr,
        ]);
    }

    /**
     * Update the HR details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateHR(Request $request, $id)
    {
        $user = Auth::user(); // Get the logged-in user (company)

        // Ensure only main HR can update HRs
        if ($user->role !== 'company' || !$user->is_main_hr) {
            abort(403, 'Unauthorized access.');
        }

        // Validate the request
        $validated = $request->validate([
            'company_hr_first_name' => 'required|string|max:255',
            'company_hr_last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'contact_number' => 'required|digits_between:10,15',
        ]);

        // Fetch the HR to update
        $hr = User::findOrFail($id);

        // Ensure the HR belongs to the same company
        if ($hr->company_email !== $user->company_email) {
            abort(403, 'Unauthorized access.');
        }

        // Update the HR details
        $hr->update($validated);

        return redirect()->route('company.manage-hrs')
            ->with('success', 'HR details updated successfully!');
    }

    /**
     * Delete the HR account.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */

    public function archiveHR($id) {
        $user = User::findOrFail($id);
        $user->archived_at = now();
        $user->save();

        return back()->with('success', 'User archived successfully.');
    }

    public function unarchive($id) {
        $user = User::findOrFail($id);
        $user->archived_at = null;
        $user->save();

        return back()->with('success', 'User restored successfully.');
    }

    public function storeDept(Request $request)
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

    public function manageDept(Department $department)
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

    public function updateDept(Request $request, Department $department)
    {
        $request->validate(['department_name' => 'required|string|max:255']);
        $department->update(['department_name' => $request->department_name]);
        return redirect()->back()->with('success', 'Department updated!');
    }

    public function destroyDept(Department $department)
    {
        $department->delete();
        return redirect()->back()->with('success', 'Department deleted!');
    }

    public function archivedDept()
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
