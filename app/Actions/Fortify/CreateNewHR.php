<?php

namespace App\Actions\Fortify;

use App\Models\HumanResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\CreatesNewUsers;



class CreateNewHR implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered admin.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): HumanResource
    {
        $currentUser = Auth::user(); 
        Log::info(message: 'User created with company_id: ' . $currentUser->company_id);

        
        $messages = [
            'first_name.required' => 'The first name field is required.',
            'last_name.required' => 'The last name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.required' => 'The password field is required.',
            'dob.required' => 'The date of birth field is required.',
            'dob.date' => 'The date of birth must be a valid date.',
            'dob.before_or_equal' => 'You must be at least 18 years old to register.',
            'gender.required' => 'The gender field is required.',
            'gender.in' => 'The selected gender is invalid.',
            'mobile_number.required' => 'The contact number field is required.',
            'mobile_number.digits_between' => 'The contact number must be in valid format (e.g., +63 912 345 6789).',
            'mobile_number.regex' => 'The contact number must be in valid format (e.g., +63 912 345 6789).'
        ];
        Validator::make($input, [
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => $this->passwordRules(),
                'first_name' => ['required', 'string', 'max:255'],    
                'middle_name' => ['nullable', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],    
                'dob' => ['required', 'date', 'before_or_equal:' . Carbon::now()->subYears(18)->format('Y-m-d')],
                'gender' => ['required', 'string', 'in:Male,Female'],
                'mobile_number' => ['required', 'digits_between:10,15', 'regex:/^9\d{9}$/'],
        ], $messages)->validate();


        $user = User::create([
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'role' => 'company',
            'is_approved' => 1,
        ]);

        $user->assignRole('company');

        $hr = HumanResource::create([
            'user_id' => $user->id,
            'company_id' => $currentUser->hr->company_id,
            'first_name' => $input['first_name'],
            'middle_name' => $input['middle_name'],
            'last_name' => $input['last_name'],
            'gender' => $input['gender'],
            'dob' => $input['dob'],
            'mobile_number' => $input['mobile_number'],
            'is_main_hr' => false,
        ]);

        return $hr;
    }
    
        

}