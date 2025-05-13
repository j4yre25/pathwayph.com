<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
        $currentUser = Auth::user(); 
        
        $messages = [
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.required' => 'The password field is required.',
            'dob.required' => 'The date of birth field is required.',
            'dob.date' => 'The date of birth must be a valid date.',
            'dob.before_or_equal' => 'You must be at least 18 years old to register.',
            'gender.required' => 'The gender field is required.',
            'gender.in' => 'The selected gender is invalid.',
            'contact_number.required' => 'The contact number field is required.',
            'contact_number.digits_between' => 'The contact number must be in valid format (e.g., +63 912 345 6789).',
            'contact_number.regex' => 'The contact number must be in valid format (e.g., +63 912 345 6789).'
        ];
        Validator::make($input, [
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => $this->passwordRules(),
                'company_hr_first_name' => ['required', 'string', 'max:255'],    
                'company_hr_last_name' => ['required', 'string', 'max:255'],    
                'dob' => ['required', 'date', 'before_or_equal:' . Carbon::now()->subYears(18)->format('Y-m-d')],
                'gender' => ['required', 'string', 'in:Male,Female'],
                'contact_number' => ['required', 'digits_between:10,15', 'regex:/^9\d{9}$/'],
        ], $messages)->validate();

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
                'company_id' => $currentUser->company_id,
            ]);

        $user->assignRole('company');
        return $user;
    }
}