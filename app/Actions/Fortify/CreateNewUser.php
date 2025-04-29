<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Carbon\Carbon;
use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Http\Request; 


class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */

     
    public function create(array $input): User
    {
        $role = $this->determineRole(request());
    
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'dob' => ['required', 'date', 'before_or_equal:' . Carbon::now()->subYears(18)->format('Y-m-d')],
            'gender' => ['required', 'string', 'in:Male,Female,Other'],
            'contact_number' => ['required', 'digits_between:10,15', 'regex:/^9\d{9}$/'],
            'telephone_number' => ['nullable', 'regex:/^(02\d{7}|0\d{2}\d{7})$/'],
        ];
    
        // Add role-specific validation rules
        switch ($role) {
            case 'graduate':
                $rules['graduate_first_name'] = ['required', 'string', 'max:255'];
                $rules['graduate_last_name'] = ['required', 'string', 'max:255'];
                $rules['graduate_middle_initial'] = ['required', 'string', 'max:255'];
                $rules['graduate_school_graduated_from'] = ['required', 'string', 'max:255'];
                $rules['graduate_program_completed'] = ['required', 'string', 'max:255'];
                
                break;
            case 'company':
                $rules['company_name'] = ['required', 'string', 'max:255'];
                $rules['company_street_address'] = ['required', 'string', 'max:255'];
                $rules['company_brgy'] = ['required', 'string', 'max:255'];
                $rules['company_city'] = ['required', 'string', 'max:255'];
                $rules['company_province'] = ['required', 'string', 'max:255'];
                $rules['company_zip_code'] = ['required', 'string', 'max:4'];
                $rules['company_email'] = ['required', 'string', 'email', 'max:255'];
                $rules['company_contact_number'] = ['required', 'numeric','digits_between:10,15', 'regex:/^9\d{9}$/'];
                $rules['company_hr_first_name'] = ['required', 'string', 'max:255'];
                $rules['company_hr_last_name'] = ['required', 'string', 'max:255'];
                break;
            case 'institution':
                $rules['institution_type'] = ['required', 'string'];
                $rules['institution_name'] = ['required', 'string'];
                $rules['institution_address'] = ['required', 'string'];
                $rules['institution_president_last_name'] = ['required', 'string', 'max:255'];
                $rules['institution_president_first_name'] = ['required', 'string', 'max:255'];
                $rules['institution_career_officer_first_name'] = ['required', 'string', 'max:255'];

                break;
            default:
                break;
        }


        // Custom validation messages
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
            'contact_number.digits_between' => 'The contact number must be between 10 and 15 digits.',
            'contact_number.regex' => 'The contact number must be in valid format (e.g., +63 912 345 6789).',
            'telephone_number.regex' => 'The telephone number must be in valid format (e.g., (02) 1234 5678 or (032) 1234 1234).',
            'graduate_first_name.required' => 'The first name field is required.',
            'graduate_last_name.required' => 'The last name field is required.',
            'graduate_middle_initial.required' => 'The middle initial field is required.',
            'graduate_school_graduated_from.required' => 'The school graduated from field is required.',
            'graduate_program_completed.required' => 'The program completed field is required.',
            'company_name.required' => 'The company name field is required.',
            'company_street_address.required' => 'The street address field is required.',
            'company_brgy.required' => 'The barangay field is required.',
            'company_city.required' => 'The city field is required.',
            'company_province.required' => 'The province field is required.',
            'company_zip_code.required' => 'The zip code field is required.',
            'company_email.required' => 'The company email field is required.', 
            'company_email.email' => 'The company email must be a valid email address.',
            'company_contact_number.required' => 'The contact number field is required.',
            'company_contact_number.regex' => 'The contact number must be in valid format (e.g., +63 912 345 6789).',
            'institution_type.required' => 'The institution type field is required.',
            'institution_name.required' => 'The institution name field is required.',
            'institution_address.required' => 'The institution address field is required.',
            'institution_president_last_name.required' => 'The president last name field is required.',
            'institution_president_first_name.required' => 'The president first name field is required.',
            'institution_career_officer_first_name.required' => 'The career officer first name field is required.',
        ];

    
        Validator::make($input, $rules, $messages)->validate();
    
    
        $userData = [
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'role' => $role,
            'dob' => $input['dob'],
            'gender' => $input['gender'],
            'contact_number' => $input['contact_number'],
            'telephone_number' => $input['telephone_number'],


        ];
    
        // Add role-specific fields
        switch ($role) {
            case 'graduate':
                $userData['graduate_first_name'] = $input['graduate_first_name'];
                $userData['graduate_last_name'] = $input['graduate_last_name'];
                $userData['graduate_middle_initial'] = $input['graduate_middle_initial'];
                $userData['graduate_school_graduated_from'] = $input['graduate_school_graduated_from'];
                $userData['graduate_program_completed'] = $input['graduate_program_completed'];
                break;
            case 'company':
                $userData['company_name'] = $input['company_name'];
                $userData['company_street_address'] = $input['company_street_address'];
                $userData['company_brgy'] = $input['company_brgy'];
                $userData['company_city'] = $input['company_city'];
                $userData['company_province'] = $input['company_province'];
                $userData['company_zip_code'] = $input['company_zip_code'];
                $userData['company_email'] = $input['company_email'];
                $userData['company_contact_number'] = $input['company_contact_number'];
                $userData['company_hr_first_name'] = $input['company_hr_first_name'];
                $userData['company_hr_last_name'] = $input['company_hr_last_name'];
                break;
            case 'institution':
                $userData['institution_type'] = $input['institution_type'];
                $userData['institution_name'] = $input['institution_name'];
                $userData['institution_address'] = $input['institution_address'];
                $userData['institution_president_last_name'] = $input['institution_president_last_name'];
                $userData['institution_president_first_name'] = $input['institution_president_first_name'];
                $userData['institution_career_officer_first_name'] = $input['institution_career_officer_first_name'];
                
                break;
            default:
                // Handle the guest role by not adding any additional fields to the user data
                break;
        }
    
        $user = User::create($userData);
    
        $user->assignRole($role);
        return $user;
    }

    protected function determineRole(Request $request): string
    {
        
        if ($request->is('register/graduate')) {
            return 'graduate';
        } elseif ($request->is('register/company')) {
            return 'company';
        } elseif ($request->is('register/institution')) {
            return 'institution';
        }

        return 'user'; // Default role if none match
    }
}