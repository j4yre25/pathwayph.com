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
        'graduate_first_name' => ['required', 'string', 'max:255'],
        'graduate_last_name' => ['required', 'string', 'max:255'],
        'graduate_middle_initial' => ['required', 'string', 'max:255'],
        'graduate_school_graduated_from' => ['required', 'integer', 'exists:users,id'],
        'graduate_program_completed' => ['required', 'exists:programs,name'], // ✅ Ensure it matches a valid program name
        'employment_status' => ['required', 'in:Employed,Underemployed,Unemployed'],
        'current_job_title' => ['required_if:employment_status,Employed,Underemployed', 'nullable', 'string', 'max:255'],
        'graduate_year_graduated' => ['required', 'exists:school_years,school_year_range'],
        'gender' => ['required', 'string', 'in:Male,Female,Other'], 
        'dob' => ['required', 'date', 'before_or_equal:' . Carbon::now()->subYears(18)->format('Y-m-d')],
        'contact_number' => ['required', 'digits_between:10,15', 'regex:/^9\d{9}$/'],
 ])->validate();

    // Create the Graduate user
    $user = User::create([
        'email' => $input['email'],
        'password' => Hash::make($this->generatePassword($input['graduate_last_name'], $input['dob'])),
        'role' => 'graduate',
        'is_approved' => 1,
        'gender' => $input['gender'],
        'dob' => $input['dob'], 
        'contact_number' => $input['contact_number'], 
        'graduate_first_name' => $input['graduate_first_name'],
        'graduate_last_name' => $input['graduate_last_name'],
        'graduate_middle_initial' => $input['graduate_middle_initial'],
        'graduate_school_graduated_from' => $input['graduate_school_graduated_from'],
        'graduate_program_completed' => $this->getProgramId($input['graduate_program_completed']),
        'graduate_year_graduated' => $this->getYearId($input['graduate_year_graduated']),
        'institution_id' => $input['graduate_school_graduated_from'],
    ]);

    DB::table('graduates')->insert([
        'user_id' => $user->id,
        'first_name' => $input['graduate_first_name'],
        'last_name' => $input['graduate_last_name'],
        'middle_initial' => $input['graduate_middle_initial'],
        'institution_id' => $input['graduate_school_graduated_from'],
        'program_id' => $this->getProgramId($input['graduate_program_completed']),
        'school_year_id' => $this->getYearId($input['graduate_year_graduated']),
        'employment_status' => $input['employment_status'],
        'current_job_title' => ($input['employment_status'] === 'Unemployed') ? 'N/A' : $input['current_job_title'],
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