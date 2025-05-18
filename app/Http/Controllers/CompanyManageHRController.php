<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\HumanResource;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user();

        $user->load('hrProfile');

        // Fetch HRs linked to the same company (filter via human_resources table)
        $companyId = $user->hrProfile ? $user->hrProfile->company_id : null;

        $hrs = User::where('role', 'company')
            ->whereHas('hrProfile', function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->where('is_main_hr', false);
            })
            ->get();

        $hrCount = $companyId ? HumanResource::where('company_id', $companyId)->count() : 0;

       

        return inertia('Company/ManageHR/Index/Index', [
            'hrs' => $hrs,
            'hrCount' => $hrCount,
            'user' => $user,
                
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
    public function destroy($id)
    {
        $user = Auth::user(); // Get the logged-in user (company)

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
