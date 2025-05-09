<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_PH');

        $companies = [
            ['name' => 'Sydney Hotel', 'address' => 'PIONEER-PENDATUN AVE.', 'brgy' => 'DADIANGAS WEST'],
            ['name' => 'KCC Mall of Gensan', 'address' => 'J. CATOLICO SR. AVE.', 'brgy' => 'LAGAO'],
            ['name' => 'SM Mall of Gensan / SM PRIME HOLDINGS, INC.', 'address' => 'SM CITY, CORNER SANTIAGO BLVD. - SAN MIGUEL ST.', 'brgy' => 'LAGAO'],
            ['name' => 'SECURITY BANK CORPORATION', 'address' => 'NATIONAL HIGHWAY CORNER RECTO ST.', 'brgy' => 'DADIANGAS NORTH'],
            ['name' => '1 Cooperative Insurance System of the Philippines Life and General Insurance', 'address' => 'DOOR 1, ACHARON BUILDING, SAMPALOC STREET', 'brgy' => 'DADIANGAS WEST'],
            ['name' => 'KCD Real Estate Lessor', 'address' => 'DAMASCO, PUROK MANGGA', 'brgy' => 'CITY HEIGHTS'],
            ['name' => 'Land Bank of the Philippines', 'address' => 'NATIONAL HIGHWAY', 'brgy' => 'CITY HEIGHTS'],
            ['name' => 'SOUTH COTABATO II ELECTRIC COOPERATIVE', 'address' => 'J. CATOLICO AVENUE', 'brgy' => 'LAGAO'],
            ['name' => 'WILCON DEPOT, INC.', 'address' => 'PUROK PALEN', 'brgy' => 'LABANGAL'],
            ['name' => 'AGRIBUSINESS RURAL BANK, INC.', 'address' => 'J. CATOLICO AVENUE', 'brgy' => 'LAGAO'],
            ['name' => 'ASIA UNITED BANK CORPORATION', 'address' => 'BELINDA ENTERPRISE BLDG., I. SANTIAGO BLVD.', 'brgy' => 'DADIANGAS SOUTH'],
            ['name' => 'DOLE PHILIPPINES, INC', 'address' => 'PUROK STA. CRUZ', 'brgy' => 'CALUMPANG'],
            ['name' => 'SAN ANDRES FISHING INDUSTRIES, INC.', 'address' => 'PUROK CABU', 'brgy' => 'TAMBLER'],
            ['name' => 'SMART COMMUNICATIONS INC.', 'address' => 'CY 9-10, SM CITY, CORNER SANTIAGO BOULEVARD - SAN MIGUEL STREET', 'brgy' => 'LAGAO'],
            ['name' => 'ST. ELIZABETH HOSPITAL, INC.', 'address' => 'NATIONAL HIGHWAY', 'brgy' => 'LAGAO'],
        ];

        foreach ($companies as $index => $comp) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $gender = $faker->randomElement(['Male', 'Female']);
            $dob = $faker->date('Y-m-d', '2000-01-01');
            $email = strtolower($firstName . '.' . $lastName . $index . '@example.com');
            $contact = '09' . $faker->randomNumber(9, true);
            $companyEmail = 'info' . $index . '@' . preg_replace('/[^a-z]/', '', strtolower(explode(' ', $comp['name'])[0])) . '.com';
            $companyContact = '9' . $faker->randomNumber(9, true);
            $companyTel = '083' . $faker->randomNumber(7, true);

            $user = User::create([
                'company_hr_first_name' => $firstName,
                'company_hr_last_name' => $lastName,
                'email' => $email,
                'contact_number'=> $contact,
                'password' => Hash::make('zxc.BNM1'),
                'gender' => $gender,
                'dob' => $dob,
                'role' => 'company',
                'company_name' => $comp['name'],
                'company_street_address' => $comp['address'],
                'company_brgy' => $comp['brgy'],
                'company_city' => 'General Santos City',
                'company_province' => 'South Cotabato',
                'company_zip_code' => '9500',
                'company_email' => $companyEmail,
                'company_contact_number' => $companyContact,
                'telephone_number' => $companyTel,
                'is_main_hr' => true,
            ]);

            $user->assignRole('company');
        }
    }
}
