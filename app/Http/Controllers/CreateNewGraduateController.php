<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Graduate;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CreateNewGraduateController extends Controller
{
    public function store(Request $request)
    {
        $rules = [
            'email' => 'required|string|email|max:255|unique:users',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'school_graduated_from' => 'required|integer|exists:institutions,id',
            'graduate_degree' => 'required|exists:degrees,id',
            'program_completed' => 'required|exists:programs,name',
            'dob' => 'required|date|before_or_equal:' . Carbon::now()->subYears(18)->format('Y-m-d'),
            'gender' => 'required|in:Male,Female',
            'mobile_number' => 'required|string|max:20',
            'year_graduated' => 'required|exists:school_years,school_year_range',
            'company_not_found' => 'nullable|boolean',
            'company_name' => 'nullable|string|max:255',
            'other_company_name' => 'nullable|string|max:255',
            'other_company_sector' => 'nullable|string|max:255',
            'current_job_title' => 'nullable|string|max:255',
            'employment_status' => 'nullable|string|max:255',
        ];

        $validated = Validator::make($request->all(), $rules)->validate();

        // Check for duplicate graduate by email and program
        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser && Graduate::where('user_id', $existingUser->id)->exists()) {
            return redirect()->back()->with('flash.banner', 'A graduate with this email already exists.');
        }

        // Generate password
        $birthYear = Carbon::parse($validated['dob'])->format('Y');
        $lastName = preg_replace('/\s+/', '', ucfirst(strtolower($validated['last_name'])));
        $generatedPassword = $lastName . $birthYear . '!!!';

        // Create user
        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($generatedPassword),
            'role' => 'graduate',
            'gender' => $validated['gender'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'is_approved' => true,
            'has_completed_information' => true,
        ]);

        // Get program and school year IDs
        $programId = $this->getProgramId($validated['program_completed']);
        $schoolYearId = $this->getYearId($validated['year_graduated']);

        // Handle company_id if company is selected from the list
        $companyId = null;
        if (empty($validated['company_not_found']) && !empty($validated['company_name'])) {
            $company = Company::where('company_name', $validated['company_name'])->first();
            if ($company) {
                $companyId = $company->id;
            }
        }

        Graduate::create([
            'user_id' => $user->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'mobile_number' => $validated['mobile_number'],
            'institution_id' => $validated['school_graduated_from'],
            'program_id' => $programId,
            'school_year_id' => $schoolYearId,
            'degree_id' => $validated['graduate_degree'],
            'employment_status' => $validated['employment_status'] ?? '',
            'current_job_title' => ($validated['employment_status'] === 'Unemployed') ? 'N/A' : $validated['current_job_title'],
            'gender' => $validated['gender'],
            'dob' => $validated['dob'],
            'company_id' => $companyId,
            'other_company_name' => !empty($validated['company_not_found']) ? $validated['other_company_name'] : null,
            'other_company_sector' => !empty($validated['company_not_found']) ? $validated['other_company_sector'] : null,
        ]);

        $user->assignRole('graduate');

        return redirect()->back()->with('flash.banner', 'Graduate created successfully!');
    }

    private function getProgramId(string $programName)
    {
        return DB::table('programs')->where('name', $programName)->value('id');
    }

    private function getYearId(string $schoolYearRange)
    {
        return DB::table('school_years')->where('school_year_range', $schoolYearRange)->value('id');
    }
}
