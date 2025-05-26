<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateNewGraduate implements CreatesNewUsers
{
    /**
     * Validate and create a newly registered admin.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'degree_id' => ['required', 'exists:degrees,id'],
            'graduate_program_completed' => ['required', 'exists:programs,name'],
            'employment_status' => ['required', 'in:Employed,Underemployed,Unemployed'],
            'current_job_title' => ['required_if:employment_status,Employed,Underemployed', 'nullable', 'string', 'max:255'],
            'graduate_year_graduated' => ['required', 'exists:school_years,school_year_range'],
            'gender' => ['required', 'string', 'in:Male,Female'],
            'dob' => ['required', 'date', 'before_or_equal:' . Carbon::now()->subYears(18)->format('Y-m-d')],
            'contact_number' => ['required', 'digits_between:10,15', 'regex:/^9\d{9}$/'],
        ])->validate();

        $institutionId = auth()->user()->institution->id;

        // Create the Graduate user
        $user = User::create([
            'email' => $input['email'],
            'password' => Hash::make($this->generatePassword($input['last_name'], $input['dob'])),
            'role' => 'graduate',
            'is_approved' => 1,
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'middle_name' => $input['middle_name'] ?? null,
        ]);

        DB::table('graduates')->insert([
            'user_id' => $user->id,
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'middle_name' => $input['middle_name'] ?? null,
            'institution_id' => $institutionId,
            'degree_id' => $input['degree_id'],
            'program_id' => $this->getProgramId($input['graduate_program_completed']),
            'gender' => $input['gender'],
            'dob' => $input['dob'],
            'school_year_id' => $this->getYearId($input['graduate_year_graduated']),
            'employment_status' => $input['employment_status'],
            'current_job_title' => ($input['employment_status'] === 'Unemployed') ? 'N/A' : $input['current_job_title'],
            'contact_number' => $input['contact_number'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user->assignRole('graduate');
        return $user;
    }

    private function getProgramId($programName)
    {
        return DB::table('programs')->where('name', $programName)->value('id');
    }
    private function getYearId($schoolYearRange)
    {
        return DB::table('school_years')->where('school_year_range', $schoolYearRange)->value('id');
    }
    private function generatePassword($lastName, $dob)
    {
        $year = Carbon::parse($dob)->year;
        return "{$lastName}{$year}!!!";
    }
}