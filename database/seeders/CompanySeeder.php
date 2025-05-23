<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\User;
use App\Models\Company;
use App\Models\HumanResource;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_PH');

        $companies = [
            ['division' => '03', 'name' => 'GS Pescador Sea Trading Corporation', 'address' => 'Pescador Building, PFDA, Barangay Tambler', 'brgy' => 'Tambler', 'tel_phone' => '(083) 552-2165', 'email' => 'gspescadorseatradingcorp@gmail.com'],
            ['division' => '03', 'name' => 'Amadeo Fishing Corporation', 'address' => 'Marang Street', 'brgy' => 'Unknown', 'tel_phone' => '(083) 553-9612', 'email' => null],
            ['division' => '03', 'name' => 'San Andres Fishing Industries, Inc.', 'address' => 'I. Santiago Blvd. Cor. R. Magsaysay Ave.', 'brgy' => 'Unknown', 'phone' => null, 'email' => null],
            ['division' => '03', 'name' => 'GS Southern Fishing Industry Inc.', 'address' => 'Talisay Street', 'brgy' => 'Unknown', 'tel_phone' => '(083) 552-2274', 'email' => null],
            ['division' => '01', 'name' => 'Evo Gene Seeds Corporation', 'address' => 'General Santos City', 'brgy' => 'Unknown', 'phone' => null, 'email' => null],
            ['division' => '01', 'name' => 'JMB Agricultural Supply', 'address' => 'Yap Mabuhay Bldg., Corner Cagampang St., Mansanitas St.', 'brgy' => 'Unknown', 'phone' => null, 'email' => null],
            ['division' => '01', 'name' => 'Syngenta Philippines Incorporated (Novartis Agro)', 'address' => 'San Isidro Street', 'brgy' => 'San Isidro', 'tel_phone' => '(083) 552-0409', 'email' => null],
            ['division' => '03', 'name' => 'Tropical Fishing Supplies Corporation', 'address' => 'Elevencionado Bldg., Cagampang Street', 'brgy' => 'Unknown', 'phone' => null, 'email' => null],
            ['division' => '10', 'name' => 'General Tuna Corporation', 'address' => 'General Santos City', 'brgy' => 'Unknown', 'mob_phone' => '+63 83 552 1234', 'email' => 'info@gentuna.com'],
            ['division' => '10', 'name' => 'Century Pacific Agricultural Ventures, Inc.', 'address' => 'General Santos City', 'brgy' => 'Unknown', 'mob_phone' => '+63 83 552 5678', 'email' => 'cpavi@gensantos.com'],
            ['division' => '13', 'name' => 'Tenpoint Manufacturing Corporation', 'address' => 'General Santos City', 'brgy' => 'Unknown', 'mob_phone' => '+63 83 553 6789', 'email' => 'contact@tenpointmfg.com'],
            ['division' => '10', 'name' => 'Celebes Canning Corporation', 'address' => 'General Santos City', 'brgy' => 'Unknown', 'mob_phone' => '+63 83 554 7890', 'email' => 'sales@celebescanning.com'],
            ['division' => '22', 'name' => 'Genpack Corporation', 'address' => 'General Santos City', 'brgy' => 'Unknown', 'mob_phone' => '+63 83 555 8901', 'email' => 'info@genpackcorp.com'],
            ['division' => '10', 'name' => 'MK Smoked Fish Corporation', 'address' => 'General Santos City', 'brgy' => 'Unknown', 'mob_phone' => '+63 83 556 9012', 'email' => 'mkfish@mksmokedfish.com'],
            ['division' => '10', 'name' => 'Rell & Renn Seafood Sphere, Inc.', 'address' => 'General Santos City', 'brgy' => 'Unknown', 'mob_phone' => '+63 83 557 0123', 'email' => 'info@rellrennseafood.com'],
            ['division' => '10', 'name' => 'Mommy Gina Tuna Resources (MGTR), Inc.', 'address' => 'General Santos City', 'brgy' => 'Unknown', 'mob_phone' => '+63 83 558 1234', 'email' => 'mgtr@mgtuna.com'],
            ['division' => '25', 'name' => 'Unilox Industrial Corporation', 'address' => 'General Santos City', 'brgy' => 'Unknown', 'mob_phone' => '+63 83 559 2345', 'email' => 'sales@uniloxindustrial.com'],
            ['division' => '10', 'name' => 'Prime Katsuobushi Philippines, Inc.', 'address' => 'General Santos City', 'brgy' => 'Unknown', 'mob_phone' => '+63 83 550 3456', 'email' => 'info@primekatsuobushi.com'],
            ['division' => '35', 'name' => 'South Cotabato I Electric Cooperative, Inc. (SOCOTECO I)', 'address' => 'Barangay Lagao', 'brgy' => 'Lagao', 'te_phone' => '(083) 552-1234', 'email' => 'info@socoteco1.com'],
            ['division' => '36', 'name' => 'General Santos City Water District (GSCWD)', 'address' => 'Barangay Lagao', 'brgy' => 'Lagao', 'tel_phone' => '(083) 552-2345', 'email' => 'info@gscwd.gov.ph'],
            ['division' => '35', 'name' => 'General Santos City Electric Cooperative, Inc. (GENSAN ELCO)', 'address' => 'Barangay Lagao', 'brgy' => 'Lagao', 'tel_phone' => '(083) 553-3456', 'email' => 'info@gensanelco.com'],
            ['division' => '39', 'name' => 'City Waste Management Office - General Santos City', 'address' => 'Jorge P. Royeca Street, Barangay Bula', 'brgy' => 'Bula', 'mob_phone' => '+63 967 903 8078', 'email' => null],
            ['division' => '41', 'name' => 'Alsons Construction and Development Corporation', 'address' => 'Alabel, Sarangani Province, General Santos City', 'brgy' => 'Unknown', 'mob_phone' => '+63 83 552 1234', 'email' => 'info@alsconsdev.com'],
            ['division' => '41', 'name' => 'Davao Del Sur Construction Corporation', 'address' => 'General Santos City', 'brgy' => 'Unknown', 'mob_phone' => '+63 83 553 5678', 'email' => 'contact@davdelsurconstruction.com'],
            ['division' => '42', 'name' => 'GenSan Builders, Inc.', 'address' => 'General Santos City', 'brgy' => 'Unknown', 'mob_phone' => '+63 83 554 6789', 'email' => 'info@gensanbuilders.com'],
            ['division' => '43', 'name' => 'Marbel Construction and Trading', 'address' => 'General Santos City', 'brgy' => 'Unknown', 'mob_phone' => '+63 83 555 7890', 'email' => 'contact@marbelconstruction.com'],
            ['division' => '41', 'name' => 'Sarangani Builders Corporation', 'address' => 'General Santos City', 'brgy' => 'Unknown', 'mob_phone' => '+63 83 556 8901', 'email' => 'info@sarangani.builders'],
            ['division' => '47', 'name' => 'Jollibee - Gaisano Branch', 'address' => 'Catolico Street, Lagao', 'brgy' => 'Lagao', 'tel_phone' => '(083) 553-4948', 'email' => null],
            ['division' => '47', 'name' => 'Jollibee - Highway Branch', 'address' => 'National Gensan Highway', 'brgy' => 'Unknown', 'tel_phone' => '(083) 301-5233', 'email' => null],
            ];

        foreach ($companies as $index => $comp) {
            
            $divisionCode = (int)$comp['division'];

            // Fetch the category and sector
            $category = Category::with('sector')->where('division_code', $divisionCode)->first();

            if (!$category || !$category->sector) {
                continue; // Skip if missing category or sector
            }
            $sector = $category->sector;


            // Generate fake personal info for HR user
            $firstName = $faker->firstName;
            $middleName = ($index < 10) ? $faker->firstName : '';
            $lastName = $faker->lastName;
            $gender = $faker->randomElement(['Male', 'Female']);
            $dob = $faker->date('Y-m-d', '2000-01-01');
            $email = strtolower($firstName . '.' . $lastName . $index . '@example.com');
            $mobileNumber = '9' . $faker->randomNumber(9, true);

            // If company email is null, generate a fallback
            $companyEmail = $comp['email'] ?? 'info' . $index . '@' . preg_replace('/[^a-z]/', '', strtolower(explode(' ', $comp['name'])[0])) . '.com';

            // If phone is null, generate fallback numbers
            $companyMobile = $comp['mob_phone'] ?? '9' . $faker->randomNumber(9, true);
            $companyTel = $comp['tel_phone'] ?? '083' . str_pad($faker->numberBetween(0, 9999999), 7, '0', STR_PAD_LEFT);

            // 1. Create the User for the HR
            $user = User::create([
                'email' => $email,
                'password' => Hash::make('zxc.BNM1'),
                'role' => 'company',
            ]);

           // 2. Create the Company (initially without company_id)
            $company = Company::create([
                'user_id' => $user->id,
                'company_name' => $comp['name'],
                'company_street_address' => $comp['address'],
                'company_brgy' => $comp['brgy'],
                'company_city' => 'General Santos City',
                'company_province' => 'South Cotabato',
                'company_zip_code' => '9500',
                'company_email' => $companyEmail,
                'company_mobile_phone' => $companyMobile,
                'company_tel_phone' => $companyTel,
                'category_id' => $category->id,
                'sector_id' => $sector->id,
            ]);

            // 3. Update company_id using your pattern: C-{paddedId}-{sector_id}{division_code}
            $paddedCompanyId = str_pad($company->id, 3, '0', STR_PAD_LEFT);
            $sectorCode = $sector->sector_id ?? '000';
            $divisionCode = $category->division_code ?? '00';

            $company->company_id = "C-{$paddedCompanyId}-{$sectorCode}{$divisionCode}";
            $company->save();

            // 3. Create the HumanResource record linked to user and company
            HumanResource::create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'mobile_number' => $mobileNumber,
                'dob' => $dob,
                'gender' => $gender,
                'is_main_hr' => false,
            ]);

            // 4. Optional: assign role using permissions package if needed
            $user->assignRole('company');
        }

    }
}
