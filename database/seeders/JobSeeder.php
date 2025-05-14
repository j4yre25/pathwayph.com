<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Job;
use App\Models\Program;
use App\Models\Company;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use App\Models\Sector;
use App\Models\Category;

class JobSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_PH');
        $sectors = Sector::pluck('id')->toArray(); // Fetch all valid sector IDs
        $categories = Category::pluck('id')->toArray(); // Fetch all valid category IDs
        $company = User::where('role', 'company')->pluck('id')->toArray(); // Fetch all valid company IDs
        $programs = Program::pluck('id')->toArray(); // Fetch all valid program IDs

        $companies = [
            ['id'=>'18','name' => 'Sydney Hotel', 'address' => 'PIONEER-PENDATUN AVE.', 'brgy' => 'DADIANGAS WEST'],
            ['id'=>'19','name' => 'KCC Mall of Gensan', 'address' => 'J. CATOLICO SR. AVE.', 'brgy' => 'LAGAO'],
            ['id'=>'20','name' => 'SM Mall of Gensan / SM PRIME HOLDINGS, INC.', 'address' => 'SM CITY, CORNER SANTIAGO BLVD. - SAN MIGUEL ST.', 'brgy' => 'LAGAO'],
            ['id'=>'21','name' => 'SECURITY BANK CORPORATION', 'address' => 'NATIONAL HIGHWAY CORNER RECTO ST.', 'brgy' => 'DADIANGAS NORTH'],
            ['id'=>'22','name' => '1 Cooperative Insurance System of the Philippines Life and General Insurance', 'address' => 'DOOR 1, ACHARON BUILDING, SAMPALOC STREET', 'brgy' => 'DADIANGAS WEST'],
            ['id'=>'23','name' => 'KCD Real Estate Lessor', 'address' => 'DAMASCO, PUROK MANGGA', 'brgy' => 'CITY HEIGHTS'],
            ['id'=>'24','name' => 'Land Bank of the Philippines', 'address' => 'NATIONAL HIGHWAY', 'brgy' => 'CITY HEIGHTS'],
            ['id'=>'25','name' => 'SOUTH COTABATO II ELECTRIC COOPERATIVE', 'address' => 'J. CATOLICO AVENUE', 'brgy' => 'LAGAO'],
            ['id'=>'26','name' => 'WILCON DEPOT, INC.', 'address' => 'PUROK PALEN', 'brgy' => 'LABANGAL'],
            ['id'=>'27','name' => 'AGRIBUSINESS RURAL BANK, INC.', 'address' => 'J. CATOLICO AVENUE', 'brgy' => 'LAGAO'],
            ['id'=>'28','name' => 'ASIA UNITED BANK CORPORATION', 'address' => 'BELINDA ENTERPRISE BLDG., I. SANTIAGO BLVD.', 'brgy' => 'DADIANGAS SOUTH'],
            ['id'=>'29','name' => 'DOLE PHILIPPINES, INC', 'address' => 'PUROK STA. CRUZ', 'brgy' => 'CALUMPANG'],
            ['id'=>'30','name' => 'SAN ANDRES FISHING INDUSTRIES, INC.', 'address' => 'PUROK CABU', 'brgy' => 'TAMBLER'],
            ['id'=>'31','name' => 'SMART COMMUNICATIONS INC.', 'address' => 'CY 9-10, SM CITY, CORNER SANTIAGO BOULEVARD - SAN MIGUEL STREET', 'brgy' => 'LAGAO'],
            ['id'=>'32','name' => 'ST. ELIZABETH HOSPITAL, INC.', 'address' => 'NATIONAL HIGHWAY', 'brgy' => 'LAGAO'],
        ];

        $jobTitlesPerCompany = [
            "Sydney Hotel" => [
                "Front Desk Officer",
                "Housekeeping Attendant",
                "Hotel Manager",
                "Room Service Staff",
                "Concierge",
                "Bellhop",
                "Night Auditor",
                "Security Personnel",
                "Kitchen Staff",
                "Event Coordinator"
            ],
            "KCC Mall of Gensan" => [
                "Sales Clerk",
                "Inventory Associate",
                "Customer Service Representative",
                "Cashier",
                "Mall Operations Assistant",
                "Store Supervisor",
                "Janitorial Staff",
                "Marketing Assistant",
                "Visual Merchandiser",
                "Security Guard"
            ],
            "SM Mall of Gensan / SM PRIME HOLDINGS, INC." => [
                "Mall Administrator",
                "Leasing Assistant",
                "Customer Service Associate",
                "Facilities Technician",
                "Security Supervisor",
                "Marketing Executive",
                "Janitor",
                "Elevator Technician",
                "Cashiering Officer",
                "Events Coordinator"
            ],
            "SECURITY BANK CORPORATION" => [
                "Bank Teller",
                "Branch Manager",
                "Loan Officer",
                "Customer Service Associate",
                "ATM Custodian",
                "Account Officer",
                "Credit Analyst",
                "Marketing Assistant",
                "Compliance Officer",
                "Financial Advisor"
            ],
            "1 Cooperative Insurance System of the Philippines Life and General Insurance" => [
                "Insurance Agent",
                "Claims Processor",
                "Policy Underwriter",
                "Member Services Officer",
                "Field Verifier",
                "Claims Adjuster",
                "Finance Officer",
                "Risk Analyst",
                "Customer Support Staff",
                "Office Clerk"
            ],
            "KCD Real Estate Lessor" => [
                "Leasing Officer",
                "Property Manager",
                "Real Estate Agent",
                "Maintenance Technician",
                "Marketing Associate",
                "Documentation Staff",
                "Front Desk Clerk",
                "Collection Officer",
                "Customer Relations Officer",
                "Building Attendant"
            ],
            "Land Bank of the Philippines" => [
                "Bank Teller",
                "Credit Officer",
                "Agricultural Loan Specialist",
                "Branch Operations Assistant",
                "IT Support Staff",
                "Risk Management Officer",
                "Loans Processor",
                "Finance Analyst",
                "Marketing Officer",
                "Audit Assistant"
            ],
            "SOUTH COTABATO II ELECTRIC COOPERATIVE" => [
                "Electrical Engineer",
                "Lineman",
                "Customer Service Staff",
                "Billing Analyst",
                "Collections Officer",
                "Meter Reader",
                "Power Systems Technician",
                "Inventory Clerk",
                "Safety Officer",
                "Maintenance Crew"
            ],
            "WILCON DEPOT, INC." => [
                "Warehouse Assistant",
                "Sales Consultant",
                "Inventory Controller",
                "Delivery Driver",
                "Cashier",
                "Product Specialist",
                "Store Supervisor",
                "Visual Merchandiser",
                "Customer Care Associate",
                "Procurement Staff"
            ],
            "AGRIBUSINESS RURAL BANK, INC." => [
                "Loans Officer",
                "Rural Banking Staff",
                "Teller",
                "Marketing Officer",
                "Field Collector",
                "Microfinance Officer",
                "Credit Investigator",
                "Compliance Assistant",
                "Finance Assistant",
                "Client Support Specialist"
            ],
            "ASIA UNITED BANK CORPORATION" => [
                "Customer Relationship Officer",
                "Teller",
                "Credit Analyst",
                "Branch Operations Supervisor",
                "Loan Processor",
                "Digital Banking Specialist",
                "Security Officer",
                "Administrative Aide",
                "Treasury Assistant",
                "Business Development Officer"
            ],
            "DOLE PHILIPPINES, INC" => [
                "Production Worker",
                "Quality Assurance Analyst",
                "Agricultural Technician",
                "Supply Chain Staff",
                "Machine Operator",
                "HR Assistant",
                "Forklift Operator",
                "Farm Supervisor",
                "Food Safety Inspector",
                "Logistics Coordinator"
            ],
            "SAN ANDRES FISHING INDUSTRIES, INC." => [
                "Factory Worker",
                "Refrigeration Technician",
                "Marine Engineer",
                "Production Supervisor",
                "Procurement Officer",
                "Quality Control Staff",
                "Inventory Assistant",
                "Net Repairman",
                "Operations Manager",
                "Logistics Staff"
            ],
            "SMART COMMUNICATIONS INC." => [
                "Retail Sales Associate",
                "Customer Support Representative",
                "Field Technician",
                "Network Engineer",
                "IT Specialist",
                "Store Supervisor",
                "Marketing Officer",
                "Data Analyst",
                "Account Executive",
                "Inventory Clerk"
            ],
            "ST. ELIZABETH HOSPITAL, INC." => [
                "Staff Nurse",
                "Medical Technologist",
                "Pharmacist",
                "Nursing Aide",
                "Billing Clerk",
                "Laboratory Assistant",
                "Radiologic Technologist",
                "Health Records Officer",
                "Hospital Admin Assistant",
                "ER Attendant"
            ]
        ];


        $jobTypes = ['full-time', 'part-time'];
        $experienceLevels = ['entry-level', 'intermediate', 'mid-level', 'senior-executive'];
        $skillsList = ['Communication', 'Teamwork', 'Problem-solving', 'Time Management', 'Customer Service', 'Leadership'];

        
        foreach ($companies as $company) {
            $companyLocation = $company['address'] . ', ' . $company['brgy'] . ', General Santos City';
            $branchLocation = $company['brgy'] . ', General Santos City';

            $hrUser = User::where('company_id', $company['id'])
                ->where('role', 'company')
                ->first();

            if (!$hrUser) {
                continue; // Skip this company if no HR user is found
            }

            $postedByName = $hrUser->company_hr_first_name . ' ' . $hrUser->company_hr_last_name;
            $userId = $hrUser->id;
            $companyId = $company['id'];

            foreach ($jobTitlesPerCompany[$company['name']] ?? [] as $title) {
                $sectorId = Arr::random($sectors);
                $categoryId = Arr::random($categories);

                $isNegotiable = (bool)random_int(0, 1);

                $minSalary = $isNegotiable ? null : random_int(5000, 20000);
                $maxSalary = $isNegotiable ? null : random_int($minSalary, $minSalary + 50000);

                $job = Job::create([
                    'user_id' => $userId,
                    'company_id' => $companyId,
                    'posted_by' => $postedByName,
                    'job_title' => $title,
                    'sector_id' => $sectorId,
                    'category_id' => $categoryId,
                    'job_type' => Arr::random($jobTypes),
                    'experience_level' => Arr::random($experienceLevels),
                    'is_salary_negotiable' => $isNegotiable,
                    'min_salary' => $minSalary,
                    'max_salary' => $maxSalary,
                    'location' => $companyLocation,
                    'branch_location' => $branchLocation,
                    'vacancy' => random_int(1, 10),
                    'description' => "$title position available at our company. Responsibilities include ...",
                    'requirements' => "Requirements for $title include ...",
                    'skills' => json_encode(array_rand(array_flip($skillsList), 3)),
                    'expiration_date' => now()->addDays(30)->format('Y-m-d'),
                    'applicants_limit' => Arr::random([null, 50, 100]),
                ]);

                $job->programs()->attach(Arr::random($programs, rand(1, min(3, count($programs)))));

            }
        }
    }
}