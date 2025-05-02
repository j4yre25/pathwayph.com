<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
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

        $currentUser = auth()->user(); // Logged-in company user

        Validator::make($input, [
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => $this->passwordRules(),
                'company_hr_first_name' => ['required', 'string', 'max:255'],    
                'company_hr_last_name' => ['required', 'string', 'max:255'],    
                'dob' => ['required', 'date', 'before_or_equal:' . Carbon::now()->subYears(18)->format('Y-m-d')],
                'gender' => ['required', 'string', 'in:Male,Female,Other'],
                'contact_number' => ['required', 'digits_between:10,15', 'regex:/^9\d{9}$/'],
            ])->validate();

            // Create the HR user
            $user = User::create([
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'role' => 'company',
                'company_hr_first_name' => $input['company_hr_first_name'],
                'company_hr_last_name' => $input['company_hr_last_name'], 
                'gender' => $input['gender'],
                'dob' => $input['dob'], 
                'contact_number' => $input['contact_number'], 
                'is_approved' => 1,
                 'is_main_hr' => false,

                  // Inherit from the current company user
                'company_name' => $currentUser->company_name,
                'company_street_address' => $currentUser->company_street_address,
                'company_brgy' => $currentUser->company_brgy,
                'company_city' => $currentUser->company_city,
                'company_province' => $currentUser->company_province,
                'company_zip_code' => $currentUser->company_zip_code,
                'company_email' => $currentUser->company_email,
                'company_contact_number' => $currentUser->company_contact_number,
                'company_description' => $currentUser->company_description,
                'company_sector' => $currentUser->company_sector,
                ]);

        $user->assignRole('company');
        return $user;
    }
}