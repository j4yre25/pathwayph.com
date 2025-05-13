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
            'graduate_first_name' => 'required|string|max:255',
            'graduate_last_name' => 'required|string|max:255',
            'graduate_middle_initial' => 'required|string|max:5',
            'graduate_school_graduated_from' => 'required|integer|exists:users,id',
            'graduate_program_completed' => 'required|exists:programs,name',
            'dob' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'contact_number' => 'nullable|string',
            'telephone_number' => 'nullable|string',
            'employment_status' => 'required|in:Employed,Underemployed,Unemployed',
            'current_job_title' => 'nullable|string|max:255',
            'graduate_year_graduated' => 'required|exists:school_years,school_year_range',
        ];

        if (User::where('email', $request->email)->exists()) {
            return redirect()->back()->with('flash.banner', 'A user with this email already exists.');
        }

        $validated = Validator::make($request->all(), $rules)->validate();

        $birthYear = Carbon::parse($validated['dob'])->format('Y');
        $lastName = preg_replace('/\s+/', '', ucfirst(strtolower($validated['graduate_last_name'])));
        $generatedPassword = $lastName . $birthYear . '!!!';

        $userData = [
            'email' => $validated['email'],
            'password' => Hash::make($generatedPassword),
            'role' => 'graduate',
            'dob' => $validated['dob'],
            'gender' => $validated['gender'],
            'contact_number' => $validated['contact_number'] ?? null,
            'telephone_number' => $validated['telephone_number'] ?? null,
            'graduate_first_name' => $validated['graduate_first_name'],
            'graduate_last_name' => $validated['graduate_last_name'],
            'graduate_middle_initial' => $validated['graduate_middle_initial'],
            'institution_id' => $validated['graduate_school_graduated_from'],
            'is_approved' => true,
        ];

        $user = User::create($userData);

        $programId = $this->getProgramId($validated['graduate_program_completed']);
        $schoolYearId = $this->getYearId($validated['graduate_year_graduated']);

        if (Graduate::where('user_id', $user->id)->exists()) {
            return redirect()->back()->with('flash.banner', 'This graduate already exists.');
        }

        Graduate::create([
            'user_id' => $user->id,
            'first_name' => $validated['graduate_first_name'],
            'last_name' => $validated['graduate_last_name'],
            'middle_initial' => $validated['graduate_middle_initial'],
            'institution_id' => $validated['graduate_school_graduated_from'],
            'program_id' => $programId,
            'school_year_id'    => $schoolYearId, // Mapped school year id
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
