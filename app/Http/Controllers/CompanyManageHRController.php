<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        $user = auth()->user(); // Get the logged-in user (company)

        // Ensure only main HR can manage HRs
        if ($user->role !== 'company' || !$user->is_main_hr) {
            abort(403, 'Unauthorized access.');
        }

        // Fetch all HRs associated with the company
        $hrs = User::where('role', 'company')
            ->where('company_name', $user->company_name)
            ->where('is_main_hr', false)
            ->get();

        return inertia('Company/ManageHR/Index/Index', [
            'hrs' => $hrs,
        ]);
    }

    /**
     * Show the form to edit an HR.
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function edit($id)
    {
        $user = auth()->user(); // Get the logged-in user (company)

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
        $user = auth()->user(); // Get the logged-in user (company)

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
    public function destroy($id)
    {
        $user = auth()->user(); // Get the logged-in user (company)

        // Ensure only main HR can delete HRs
        if ($user->role !== 'company' || !$user->is_main_hr) {
            abort(403, 'Unauthorized access.');
        }

        // Fetch the HR to delete
        $hr = User::findOrFail($id);

        // Ensure the HR belongs to the same company
        if ($hr->company_email !== $user->company_email) {
            abort(403, 'Unauthorized access.');
        }

        // Delete the HR account
        $hr->delete();

        return redirect()->route('company.manage-hrs')
            ->with('success', 'HR account deleted successfully!');
    }
}
