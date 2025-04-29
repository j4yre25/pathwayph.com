<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewCareerOfficer implements CreatesNewUsers
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
        'institution_career_officer_first_name' => ['required', 'string', 'max:255'],    // Update 04/04
        'institution_career_officer_last_name' => ['required', 'string', 'max:255'],    // Update 04/04
    ])->validate();

    // Create the HR user
    $user = User::create([
        'email' => $input['email'],
        'password' => Hash::make($input['password']),
        'role' => 'hr',
        'is_approved' => 1,
        'institution_career_officer_first_name' => $input['institution_career_officer_first_name'], // Update 04/04
        'institution_career_officer_last_name' => $input['institution_career_officer_last_name'], // Update 04/04
    ]);


        $user->assignRole('institution');
        return $user;
    }
}



