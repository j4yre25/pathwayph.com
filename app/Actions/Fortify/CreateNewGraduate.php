<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Http\Request;

class CreateNewGraduate implements CreatesNewUsers
{
    use PasswordValidationRules;
    public function create(array $input): User
    {
            $role = $this->determineRole(request());

            $rules = [
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => $this->passwordRules(),
            ];
    
            // Add role-specific validation rules
            switch ($role) {
                    case 'graduate':
                        $rules['graduate_first_name'] = ['required', 'string', 'max:255'];
                        $rules['graduate_last_name'] = ['required', 'string', 'max:255'];
                        break;
                default:
                    break;
            }
    
            Validator::make($input, $rules)->validate();
    
            Validator::make($input, $rules)->validate();
    
        $userData = [
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'role' => $role,

        ];
    
        // Add role-specific fields
        switch ($role) {
            case 'graduate':
                $userData['graduate_first_name'] = $input['graduate_first_name'];
                $userData['graduate_last_name'] = $input['graduate_last_name'];
                break;
            }
    
            $user = User::create($userData);
    
            // Assign the role to the user
            $user->assignRole($role);
    
            return $user;
        }
    
        
        protected function determineRole(Request $request): string
    {
        
        if ($request->is('register/graduate')) {
            return 'graduate';
        }

        return 'user'; // Default role if none match
    }
    }



