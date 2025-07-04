<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\HrInfoView;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class CompanyManageHRController extends Controller
{
    /**
     * Show the list of HRs under the company.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        /** @var \App\Models\User $user */
         $user = Auth::user();

        // Get the company_id linked to the currently logged-in HR
        $companyId = $user->hr->company_id;

        // Query your view and filter by company_id
        $hrs = DB::table('company_hr_view')
            ->where('company_id', $companyId)
            ->get();

        return inertia('Company/ManageHR/Index/Index', [
            'hrs' => $hrs,
        ]);
    }


    public function edit($id)
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
    public function update(Request $request, $id)
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

    public function archive($id) {
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

}
