<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewHR implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered admin.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
Validator::make($input, [
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => $this->passwordRules(),
        'company_hr_first_name' => ['required', 'string', 'max:255'],    // Update 04/04
        'company_hr_last_name' => ['required', 'string', 'max:255'],    // Update 04/04
        'gender' => ['required', 'string', 'in:Male,Female,Other'], // Update 04/04
        'dob' => ['required', 'date'], // Update 04/04
        'contact_number' => ['required', 'string', 'max:15', 'regex:/^[0-9]{10,15}$/'], // Update 04/04
    ])->validate();

    // Create the HR user
    $user = User::create([
        'email' => $input['email'],
        'password' => Hash::make($input['password']),
        'role' => 'hr',
        'is_approved' => 1,
        'company_hr_first_name' => $input['company_hr_first_name'], // Update 04/04
        'company_hr_last_name' => $input['company_hr_last_name'], // Update 04/04
        'gender' => $input['gender'], // Update 04/04
        'dob' => $input['dob'], // Update 04/04
        'contact_number' => $input['contact_number'], // Update 04/04
    ]);



        $user->assignRole('company');
        return $user;
    }
}