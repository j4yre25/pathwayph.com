<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $users = [
       
            [
                'email' => 'peso@example.com',
                'password' => 'password123',
                'role' => 'peso',
                'peso_first_name'  => 'Peso ',
                'peso_last_name'  => 'Admin',
            ],
        ];

        foreach ($users as $input) {
            $rules = [
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8'], // Adjust password rules as needed
                'role' => ['required', 'string', 'in:peso,graduate,company,institution'],
            ];

            switch ($input['role']) {
                case 'graduate':
                    $rules['graduate_first_name'] = ['required', 'string', 'max:255'];
                    $rules['graduate_last_name'] = ['required', 'string', 'max:255'];
                    $rules['graduate_school_graduated_from'] = ['required', 'string'];
                    $rules['graduate_program_completed'] = ['required', 'string'];
                    $rules['graduate_year_graduated'] = ['required', 'string'];
                    $rules['graduate_skills'] = ['required', 'string'];
                    break;
                case 'company':
                    $rules['company_name'] = ['required', 'string'];
                    $rules['company_address'] = ['required', 'string'];
                    $rules['company_sector'] = ['required', 'string'];
                    $rules['company_category'] = ['required', 'string'];
                    $rules['company_contact_number'] = ['required', 'string'];
                    $rules['company_hr_last_name'] = ['required', 'string', 'max:255'];
                    $rules['company_hr_first_name'] = ['required', 'string', 'max:255'];
                    $rules['company_hr_middle_initial'] = ['required', 'string'];
                    break;
                case 'institution':
                    $rules['institution_type'] = ['required', 'string'];
                    $rules['institution_address'] = ['required', 'string'];
                    $rules['institution_contact_number'] = ['required', 'string'];
                    $rules['institution_president_last_name'] = ['required', 'string', 'max:255'];
                    $rules['institution_president_first_name'] = ['required', 'string', 'max:255'];
                    $rules['institution_career_officer_first_name'] = ['required', 'string', 'max:255'];
                    break;
            }

            // Validate the input
            Validator::make($input, $rules)->validate();

            // Create the user
            User::create([
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'role' => $input['role'],
                'peso_first_name' => $input['role'] === 'peso' && isset($input['peso_first_name']) ? $input['peso_first_name'] : '',
                'peso_last_name' => $input['role'] === 'peso' && isset($input['peso_last_name']) ? $input['peso_last_name'] : '',

            ]);
        }
    }
}
