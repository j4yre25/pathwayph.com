<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Graduate;
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
            'school_graduated_from' => 'required|integer|exists:users,id',
            'program_completed' => 'required|exists:programs,name',
            'dob' => 'required|date|before_or_equal:' . Carbon::now()->subYears(18)->format('Y-m-d'),
            'gender' => 'required|in:Male,Female',
            'contact_number' => 'nullable|string',
            'telephone_number' => 'nullable|string',
            'employment_status' => 'required|in:Employed,Underemployed,Unemployed',
            'current_job_title' => 'nullable|string|max:255',
            'year_graduated' => 'required|exists:school_years,school_year_range',
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
            'dob' => $validated['dob'],
            'gender' => $validated['gender'],
            'contact_number' => $validated['contact_number'] ?? null,
            'telephone_number' => $validated['telephone_number'] ?? null,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'institution_id' => $validated['school_graduated_from'],
            'is_approved' => true,
        ]);

        // Get program and school year IDs
        $programId = $this->getProgramId($validated['program_completed']);
        $schoolYearId = $this->getYearId($validated['year_graduated']);

        Graduate::create([
            'user_id' => $user->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'institution_id' => $validated['school_graduated_from'],
            'program_id' => $programId,
            'school_year_id' => $schoolYearId,
            'employment_status' => $validated['employment_status'],
            'current_job_title' => ($validated['employment_status'] === 'Unemployed') ? 'N/A' : $validated['current_job_title'],
            'gender' => $validated['gender'],
            'dob' => $validated['dob'],
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
