<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Http\Request;

class CreateNewAdmin implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered admin.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // Determine the role based on the request
        $role = $this->determineRole(request());

        // Validation rules for admin registration
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'dob' => ['required', 'date'],
            'is_approved' => ['boolean'],
            'gender' => ['required', 'string', 'in:Male,Female,Other'],
            'contact_number' => ['required', 'digits_between:10,15'],
            'telephone_number' => ['nullable', 'digits_between:7,15'],
        ];

        // Add role-specific validation rules
        switch ($role) {
            case 'peso':
                $rules['peso_first_name'] = ['required', 'string', 'max:255'];
                $rules['peso_last_name'] = ['required', 'string', 'max:255'];
                break;
            // You can add more roles and their specific validation rules here if needed
            default:
                // Handle the default case if necessary
                break;
        }

        Validator::make($input, $rules)->validate();

        // Create the admin user
        $userData = [
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'role' => $role,
            'is_approved' => true,
            'dob' => $input['dob'],
            'gender' => $input['gender'],
            'contact_number' => $input['contact_number'],
            'telephone_number' => $input['telephone_number'],
    
            
        ];

        // Add role-specific fields
        switch ($role) {
            case 'peso':
                $userData['peso_first_name'] = $input['peso_first_name'];
                $userData['peso_last_name'] = $input['peso_last_name'];
              
            // Add more cases for other roles if needed
            default:
                // Handle the default case if necessary
                break;
        }


        $user = User::create($userData);

        // Assign the role to the user
        $user->assignRole($role);

        return $user;
    }

    protected function determineRole(Request $request): string
    {
        // Determine the role based on the request path
        if ($request->is('peso/register')) {
            return 'peso';
        }


        return 'admin'; // Default role if none match
    }
}