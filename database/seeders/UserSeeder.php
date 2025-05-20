<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Peso;

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
                'peso_first_name' => 'Peso',
                'peso_last_name' => 'Admin',
                'peso_middle_name' => 'M.',
                'description' => 'Main PESO officer for General Santos City.',
                'contact_number' => '09123456789',
                'telephone_number' => '0831234567',
                'address' => '123 Gensan Street, Barangay Lagao, General Santos City',
            ],
        ];

        foreach ($users as $input) {
            $rules = [
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8'],
                'role' => ['required', 'string', 'in:peso,graduate,company,institution'],
            ];

            Validator::make($input, $rules)->validate();

            // Create user
            $user = User::create([
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'role' => $input['role'],
            ]);

            // If user is peso, create corresponding peso record
            if ($user->role === 'peso') {
                Peso::create([
                    'user_id' => $user->id,
                    'peso_first_name' => $input['peso_first_name'],
                    'peso_middle_name' => $input['peso_middle_name'],
                    'peso_last_name' => $input['peso_last_name'],
                    'description' => $input['description'],
                    'contact_number' => $input['contact_number'],
                    'telephone_number' => $input['telephone_number'],
                    'address' => $input['address'],
                ]);
            }
        }
    }
}
