<?php

namespace Database\Seeders;

use App\Models\HumanResource;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Job;
use App\Models\Program;
use App\Models\Company;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\Sector;
use App\Models\Category;

class JobSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_PH');
        $sectors = Sector::pluck('id')->toArray(); // Fetch all valid sector IDs
        $company = User::where('role', 'company')->pluck('id')->toArray(); // Fetch all valid company IDs

         $companies = [
            ['name' => 'GS Pescador Sea Trading Corporation', 'address' => 'Pescador Building, PFDA, Barangay Tambler'],
            ['name' => 'Amadeo Fishing Corporation', 'address' => 'Marang Street', 'brgy' => 'Calumpang'],
            ['name' => 'San Andres Fishing Industries, Inc.', 'address' => 'I. Santiago Blvd. Cor. R. Magsaysay Ave. Barangay Dadiangas South.'],
            ['name' => 'GS Southern Fishing Industry Inc.', 'address' => 'Talisay Street Barangay Lagao'],
            ['name' => 'Evo Gene Seeds Corporation', 'address' => 'Purok 14 Barangay Katangawan'],
            ['name' => 'JMB Agricultural Supply', 'address' => 'Yap Mabuhay Bldg., Corner Cagampang St., Mansanitas St.Barangay Dadiangas South'],
            ['name' => 'Syngenta Philippines Incorporated (Novartis Agro)', 'address' => 'San Isidro Street Barangay San Isidro'],
            ['name' => 'Tropical Fishing Supplies Corporation', 'address' => 'Elevencionado Bldg., Cagampang Street Barangay Dadiangas South'],
            ['name' => 'General Tuna Corporation', 'address' => 'Barangay Tambler General Santos City'],
            ['name' => 'Century Pacific Agricultural Ventures, Inc.', 'address' => 'Barangay Tambler General Santos City'],
            ['name' => 'Tenpoint Manufacturing Corporation', 'address' => 'Barangay Labnagal General Santos City'],
            ['name' => 'Celebes Canning Corporation', 'address' => 'Barangay Tambler General Santos City'],
            ['name' => 'Genpack Corporation', 'address' => 'Barangay Tambler General Santos City'],
            ['name' => 'MK Smoked Fish Corporation', 'address' => 'Barangay Tambler General Santos City'],
            ['name' => 'Rell & Renn Seafood Sphere, Inc.', 'address' => 'Barangay Tambler General Santos City'],
            ['name' => 'Mommy Gina Tuna Resources (MGTR), Inc.', 'address' => 'Barangay Tambler General Santos City'],
            ['name' => 'Unilox Industrial Corporation', 'address' => 'Barangay City Heights General Santos City'],
            ['name' => 'Prime Katsuobushi Philippines, Inc.', 'address' => 'Barangay Lagao General Santos City'],
            ['name' => 'South Cotabato I Electric Cooperative, Inc. (SOCOTECO I)', 'address' => 'Barangay Lagao General Santos City'],
            ['name' => 'General Santos City Water District (GSCWD)', 'address' => 'Barangay Lagao General Santos City'],
            ['name' => 'General Santos City Electric Cooperative, Inc. (GENSAN ELCO)', 'address' => 'Barangay Lagao', 'brgy' => 'Lagao'],
            ['name' => 'City Waste Management Office - General Santos City', 'address' => 'Jorge P. Royeca Street, Barangay Bula General Santos City'],
            ['name' => 'Alsons Construction and Development Corporation', 'address' => 'Alabel, Sarangani Province, General Santos City'],
            ['name' => 'Davao Del Sur Construction Corporation', 'address' => 'General Santos City'],
            ['name' => 'GenSan Builders, Inc.', 'address' => 'General Santos City'],
            ['name' => 'Marbel Construction and Trading', 'address' => 'General Santos City'],
            ['name' => 'Sarangani Builders Corporation', 'address' => 'General Santos City'],
            ['name' => 'Jollibee - Gaisano Branch', 'address' => 'Catolico Street, Barangay Lagao, General Santos City'],
            ['name' => 'Jollibee - Highway Branch', 'address' => 'National Gensan Highway, General Santos City, South Cotabato, Philippines'],
        ];
        

        $jobTitlesPerCompanyWithDivision = [
             "General Tuna Corporation" => [
                ["title" => "Production Supervisor", "category_id" => 10, "sector_id" => 1],
                ["title" => "Quality Assurance Analyst", "category_id" => 10, "sector_id" => 1],
                ["title" => "Tuna Cutter", "category_id" => 10, "sector_id" => 1],
                ["title" => "Packaging Machine Operator", "category_id" => 10, "sector_id" => 1],
                ["title" => "Export Documentation Clerk", "category_id" => 52, "sector_id" => 4],
                ["title" => "Refrigeration Technician", "category_id" => 33, "sector_id" => 3],
                ["title" => "Warehouse Staff", "category_id" => 52, "sector_id" => 4],
                ["title" => "Forklift Operator", "category_id" => 52, "sector_id" => 4],
                ["title" => "Food Safety Inspector", "category_id" => 10, "sector_id" => 1],
                ["title" => "HR Assistant", "category_id" => 82, "sector_id" => 8],
            ],
            "Syngenta Philippines Incorporated (Novartis Agro)" => [
                ["title" => "Agricultural Technician", "category_id" => 1, "sector_id" => 1], // Crop/Animal production
                ["title" => "Sales Agronomist", "category_id" => 46, "sector_id" => 6],      // Wholesale and retail trade
                ["title" => "Field Research Assistant", "category_id" => 72, "sector_id" => 7], // Scientific research and development
                ["title" => "Crop Protection Specialist", "category_id" => 1, "sector_id" => 1],
                ["title" => "Seed Production Supervisor", "category_id" => 1, "sector_id" => 1],
                ["title" => "Regulatory Affairs Officer", "category_id" => 84, "sector_id" => 8], // Public administration
                ["title" => "Logistics Coordinator", "category_id" => 52, "sector_id" => 4],    // Transportation and storage
                ["title" => "Laboratory Assistant", "category_id" => 72, "sector_id" => 7],
                ["title" => "Territory Sales Manager", "category_id" => 46, "sector_id" => 6],
                ["title" => "Marketing Analyst", "category_id" => 73, "sector_id" => 7],       // Advertising and market research
            ],
        "Celebes Canning Corporation" => [
                ["title" => "Canning Line Operator", "category_id" => 10, "sector_id" => 3], // Food manufacturing
                ["title" => "Machine Maintenance Technician", "category_id" => 33, "sector_id" => 3],
                ["title" => "Production Planner", "category_id" => 10, "sector_id" => 3],
                ["title" => "Logistics Clerk", "category_id" => 52, "sector_id" => 4],
                ["title" => "Food Chemist", "category_id" => 72, "sector_id" => 7],
                ["title" => "Quality Control Inspector", "category_id" => 10, "sector_id" => 3],
                ["title" => "Supply Chain Assistant", "category_id" => 52, "sector_id" => 4],
                ["title" => "Forklift Operator", "category_id" => 52, "sector_id" => 4],
                ["title" => "Export Compliance Officer", "category_id" => 52, "sector_id" => 4],
                ["title" => "Warehouse Coordinator", "category_id" => 52, "sector_id" => 4],
            ],

            "Evo Gene Seeds Corporation" => [
                ["title" => "Plant Breeder", "category_id" => 1, "sector_id" => 1], // Agriculture
                ["title" => "Seed Lab Technician", "category_id" => 72, "sector_id" => 7], // Scientific R&D
                ["title" => "Field Supervisor", "category_id" => 1, "sector_id" => 1],
                ["title" => "Sales Representative", "category_id" => 46, "sector_id" => 6], // Wholesale/Retail Trade
                ["title" => "Agronomist", "category_id" => 1, "sector_id" => 1],
                ["title" => "Quality Assurance Inspector", "category_id" => 10, "sector_id" => 3], // Food Manufacturing
                ["title" => "Packaging Clerk", "category_id" => 10, "sector_id" => 3],
                ["title" => "Research Assistant", "category_id" => 72, "sector_id" => 7],
                ["title" => "Seed Production Technician", "category_id" => 1, "sector_id" => 1],
                ["title" => "Logistics Staff", "category_id" => 52, "sector_id" => 4], // Transport/Warehousing
            ],

            "GS Pescador Sea Trading Corporation" => [
                ["title" => "Fish Processor", "category_id" => 10, "sector_id" => 3],
                ["title" => "Cold Storage Manager", "category_id" => 52, "sector_id" => 4],
                ["title" => "Packaging Operator", "category_id" => 10, "sector_id" => 3],
                ["title" => "Quality Inspector", "category_id" => 10, "sector_id" => 3],
                ["title" => "Logistics Driver", "category_id" => 52, "sector_id" => 4],
                ["title" => "Warehouse Keeper", "category_id" => 52, "sector_id" => 4],
                ["title" => "Export Assistant", "category_id" => 52, "sector_id" => 4],
                ["title" => "Sanitation Worker", "category_id" => 10, "sector_id" => 3],
                ["title" => "Production Supervisor", "category_id" => 10, "sector_id" => 3],
                ["title" => "Compliance Staff", "category_id" => 82, "sector_id" => 7],
            ],

            "San Andres Fishing Industries, Inc." => [
                ["title" => "Fishing Vessel Captain", "category_id" => 3, "sector_id" => 1],
                ["title" => "Marine Diesel Mechanic", "category_id" => 33, "sector_id" => 3],
                ["title" => "Deckhand", "category_id" => 3, "sector_id" => 1],
                ["title" => "Fish Sorter", "category_id" => 10, "sector_id" => 3],
                ["title" => "Cold Storage Technician", "category_id" => 33, "sector_id" => 3],
                ["title" => "Net Repair Specialist", "category_id" => 33, "sector_id" => 3],
                ["title" => "Safety Officer", "category_id" => 82, "sector_id" => 7],
                ["title" => "Fleet Operations Assistant", "category_id" => 52, "sector_id" => 4],
                ["title" => "Warehouse Loader", "category_id" => 52, "sector_id" => 4],
                ["title" => "Logistics Planner", "category_id" => 52, "sector_id" => 4],
            ],

            "GS Southern Fishing Industry Inc." => [
                ["title" => "Quality Assurance Staff", "category_id" => 10, "sector_id" => 3],
                ["title" => "Processing Plant Operator", "category_id" => 10, "sector_id" => 3],
                ["title" => "Forklift Driver", "category_id" => 52, "sector_id" => 4],
                ["title" => "Fish Cleaner", "category_id" => 10, "sector_id" => 3],
                ["title" => "Freezer Operator", "category_id" => 10, "sector_id" => 3],
                ["title" => "Logistics Coordinator", "category_id" => 52, "sector_id" => 4],
                ["title" => "Operations Supervisor", "category_id" => 10, "sector_id" => 3],
                ["title" => "Packaging Staff", "category_id" => 10, "sector_id" => 3],
                ["title" => "Inventory Clerk", "category_id" => 52, "sector_id" => 4],
                ["title" => "Export Documentation Assistant", "category_id" => 52, "sector_id" => 4],
            ],

            "Tropical Fishing Supplies Corporation" => [
                ["title" => "Inventory Clerk", "category_id" => 52, "sector_id" => 4],
                ["title" => "Procurement Officer", "category_id" => 46, "sector_id" => 6],
                ["title" => "Sales Associate", "category_id" => 46, "sector_id" => 6],
                ["title" => "Warehouseman", "category_id" => 52, "sector_id" => 4],
                ["title" => "Supply Chain Analyst", "category_id" => 52, "sector_id" => 4],
                ["title" => "Technical Sales Representative", "category_id" => 46, "sector_id" => 6],
                ["title" => "Admin Assistant", "category_id" => 82, "sector_id" => 7],
                ["title" => "Marketing Staff", "category_id" => 63, "sector_id" => 6],
                ["title" => "Delivery Coordinator", "category_id" => 52, "sector_id" => 4],
                ["title" => "Customer Support Staff", "category_id" => 63, "sector_id" => 6],
            ],

            "Prime Katsuobushi Philippines, Inc." => [
                ["title" => "Drying Technician", "category_id" => 10, "sector_id" => 3],
                ["title" => "Production Line Staff", "category_id" => 10, "sector_id" => 3],
                ["title" => "Packaging Operator", "category_id" => 10, "sector_id" => 3],
                ["title" => "Quality Assurance Technician", "category_id" => 10, "sector_id" => 3],
                ["title" => "Warehouse Staff", "category_id" => 52, "sector_id" => 4],
                ["title" => "Export Assistant", "category_id" => 52, "sector_id" => 4],
                ["title" => "Sanitation Crew", "category_id" => 10, "sector_id" => 3],
                ["title" => "Maintenance Technician", "category_id" => 33, "sector_id" => 3],
                ["title" => "Forklift Operator", "category_id" => 52, "sector_id" => 4],
                ["title" => "Production Supervisor", "category_id" => 10, "sector_id" => 3],
            ],

        "MK Smoked Fish Corporation" => [
                ["title" => "Fish Smoking Technician", "category_id" => 10, "sector_id" => 3],
                ["title" => "Production Worker", "category_id" => 10, "sector_id" => 3],
                ["title" => "QA Staff", "category_id" => 10, "sector_id" => 3],
                ["title" => "Packaging Crew", "category_id" => 10, "sector_id" => 3],
                ["title" => "Warehouse Assistant", "category_id" => 52, "sector_id" => 4],
                ["title" => "Export Clerk", "category_id" => 52, "sector_id" => 4],
                ["title" => "Sanitation Officer", "category_id" => 10, "sector_id" => 3],
                ["title" => "Maintenance Helper", "category_id" => 33, "sector_id" => 3],
                ["title" => "Cold Storage Handler", "category_id" => 52, "sector_id" => 4],
                ["title" => "Production Scheduler", "category_id" => 10, "sector_id" => 3],
            ],

            "Rell & Renn Seafood Sphere, Inc." => [
                ["title" => "Export Sales Officer", "category_id" => 46, "sector_id" => 6],
                ["title" => "Quality Assurance Clerk", "category_id" => 10, "sector_id" => 3],
                ["title" => "Refrigeration Technician", "category_id" => 33, "sector_id" => 3],
                ["title" => "Packaging Line Worker", "category_id" => 10, "sector_id" => 3],
                ["title" => "Cold Storage Staff", "category_id" => 52, "sector_id" => 4],
                ["title" => "Forklift Operator", "category_id" => 52, "sector_id" => 4],
                ["title" => "Logistics Assistant", "category_id" => 52, "sector_id" => 4],
                ["title" => "Documentation Officer", "category_id" => 52, "sector_id" => 4],
                ["title" => "Processing Plant Supervisor", "category_id" => 10, "sector_id" => 3],
                ["title" => "Operations Staff", "category_id" => 10, "sector_id" => 3],
            ],

            "Mommy Gina Tuna Resources (MGTR), Inc." => [
                ["title" => "Tuna Processor", "category_id" => 10, "sector_id" => 3],
                ["title" => "Logistics Coordinator", "category_id" => 52, "sector_id" => 4],
                ["title" => "QA Analyst", "category_id" => 10, "sector_id" => 3],
                ["title" => "Export Documentation Officer", "category_id" => 52, "sector_id" => 4],
                ["title" => "Production Crew", "category_id" => 10, "sector_id" => 3],
                ["title" => "Fish Sorter", "category_id" => 10, "sector_id" => 3],
                ["title" => "Packaging Technician", "category_id" => 10, "sector_id" => 3],
                ["title" => "Maintenance Crew", "category_id" => 33, "sector_id" => 3],
                ["title" => "Cold Room Technician", "category_id" => 33, "sector_id" => 3],
                ["title" => "Fleet Dispatcher", "category_id" => 52, "sector_id" => 4],
            ],

            "JMB Agricultural Supply" => [
                ["title" => "Sales Associate", "category_id" => 46, "sector_id" => 6],
                ["title" => "Storekeeper", "category_id" => 52, "sector_id" => 4],
                ["title" => "Inventory Clerk", "category_id" => 52, "sector_id" => 4],
                ["title" => "Delivery Driver", "category_id" => 52, "sector_id" => 4],
                ["title" => "Product Promoter", "category_id" => 46, "sector_id" => 6],
                ["title" => "Cashier", "category_id" => 46, "sector_id" => 6],
                ["title" => "Field Sales Representative", "category_id" => 46, "sector_id" => 6],
                ["title" => "Customer Service Officer", "category_id" => 63, "sector_id" => 6],
                ["title" => "Merchandising Assistant", "category_id" => 46, "sector_id" => 6],
                ["title" => "Warehouse Assistant", "category_id" => 52, "sector_id" => 4],
            ],
            "Century Pacific Agricultural Ventures, Inc." => [
                ["title" => "Production Operator", "category_id" => 10, "sector_id" => 3],
                ["title" => "Coconut Sorter", "category_id" => 10, "sector_id" => 3],
                ["title" => "Forklift Driver", "category_id" => 52, "sector_id" => 4],
                ["title" => "Machine Technician", "category_id" => 33, "sector_id" => 3],
                ["title" => "QA Inspector", "category_id" => 10, "sector_id" => 3],
                ["title" => "Packaging Staff", "category_id" => 10, "sector_id" => 3],
                ["title" => "Maintenance Personnel", "category_id" => 33, "sector_id" => 3],
                ["title" => "Production Supervisor", "category_id" => 10, "sector_id" => 3],
                ["title" => "Sanitation Team Member", "category_id" => 10, "sector_id" => 3],
                ["title" => "Export Assistant", "category_id" => 52, "sector_id" => 4],
            ],

            "Tenpoint Manufacturing Corporation" => [
                ["title" => "Machine Operator", "category_id" => 10, "sector_id" => 3],
                ["title" => "Production Staff", "category_id" => 10, "sector_id" => 3],
                ["title" => "Warehouse Clerk", "category_id" => 52, "sector_id" => 4],
                ["title" => "Quality Inspector", "category_id" => 10, "sector_id" => 3],
                ["title" => "Logistics Assistant", "category_id" => 52, "sector_id" => 4],
                ["title" => "Forklift Operator", "category_id" => 52, "sector_id" => 4],
                ["title" => "Maintenance Technician", "category_id" => 33, "sector_id" => 3],
                ["title" => "Shift Supervisor", "category_id" => 10, "sector_id" => 3],
                ["title" => "Inventory Staff", "category_id" => 52, "sector_id" => 4],
                ["title" => "HR Coordinator", "category_id" => 82, "sector_id" => 7],
            ],

            "Genpack Corporation" => [
                ["title" => "Packaging Technician", "category_id" => 10, "sector_id" => 3],
                ["title" => "Machine Operator", "category_id" => 10, "sector_id" => 3],
                ["title" => "Production Assistant", "category_id" => 10, "sector_id" => 3],
                ["title" => "Inventory Specialist", "category_id" => 52, "sector_id" => 4],
                ["title" => "Warehouseman", "category_id" => 52, "sector_id" => 4],
                ["title" => "Maintenance Crew", "category_id" => 33, "sector_id" => 3],
                ["title" => "Product Tester", "category_id" => 10, "sector_id" => 3],
                ["title" => "Logistics Assistant", "category_id" => 52, "sector_id" => 4],
                ["title" => "Shipping Clerk", "category_id" => 52, "sector_id" => 4],
                ["title" => "Production Planner", "category_id" => 10, "sector_id" => 3],
            ],

            "Unilox Industrial Corporation" => [
                ["title" => "Chemical Technician", "category_id" => 20, "sector_id" => 3],
                ["title" => "Production Operator", "category_id" => 10, "sector_id" => 3],
                ["title" => "Quality Control Analyst", "category_id" => 10, "sector_id" => 3],
                ["title" => "Warehouse Staff", "category_id" => 52, "sector_id" => 4],
                ["title" => "Forklift Driver", "category_id" => 52, "sector_id" => 4],
                ["title" => "Logistics Officer", "category_id" => 52, "sector_id" => 4],
                ["title" => "Safety and Compliance Officer", "category_id" => 10, "sector_id" => 3],
                ["title" => "Machine Maintenance Technician", "category_id" => 33, "sector_id" => 3],
                ["title" => "Admin Clerk", "category_id" => 82, "sector_id" => 7],
                ["title" => "Product Development Assistant", "category_id" => 20, "sector_id" => 3],
            ],

             "South Cotabato I Electric Cooperative, Inc. (SOCOTECO I)" => [
                ["title" => "Electrical Engineer", "category_id" => 35],
                ["title" => "Meter Reader", "category_id" => 35],
                ["title" => "Customer Service Representative", "category_id" => 82],
                ["title" => "Line Technician", "category_id" => 35],
                ["title" => "Billing Clerk", "category_id" => 82],
                ["title" => "Safety Officer", "category_id" => 20],
                ["title" => "Administrative Assistant", "category_id" => 82],
                ["title" => "Accountant", "category_id" => 69],
                ["title" => "IT Support Specialist", "category_id" => 62],
                ["title" => "Operations Manager", "category_id" => 70],
            ],

           "General Santos City Water District (GSCWD)" => [
                ["title" => "Water Systems Engineer", "category_id" => 36],         // Water supply
                ["title" => "Field Technician", "category_id" => 36],               // Water supply
                ["title" => "Customer Relations Officer", "category_id" => 82],     // Office/Admin
                ["title" => "Billing Specialist", "category_id" => 82],             // Office/Admin
                ["title" => "Laboratory Technician", "category_id" => 72],          // Scientific Research (lab tech fits here)
                ["title" => "Maintenance Technician", "category_id" => 33],         // Repair and Installation of Machinery
                ["title" => "Administrative Officer", "category_id" => 82],         // Office/Admin
                ["title" => "Safety Officer", "category_id" => 20],                 
                ["title" => "IT Administrator", "category_id" => 62],               // IT/Computer Consultancy
                ["title" => "Operations Supervisor", "category_id" => 70],          // Management Consultancy
            ],

            "General Santos City Electric Cooperative, Inc. (GENSAN ELCO)" => [
                ["title" => "Electrical Engineer", "category_id" => 35],            // Electricity
                ["title" => "Line Crew Leader", "category_id" => 35],               // Electricity
                ["title" => "Customer Service Representative", "category_id" => 82],// Office/Admin
                ["title" => "Meter Technician", "category_id" => 35],               // Electricity
                ["title" => "Billing Clerk", "category_id" => 82],                  // Office/Admin
                ["title" => "Safety Officer", "category_id" => 20],                 // Membership Organization
                ["title" => "Administrative Assistant", "category_id" => 82],       // Office/Admin
                ["title" => "Accountant", "category_id" => 69],                     // Accounting & Legal
                ["title" => "IT Support Specialist", "category_id" => 62],          // IT Support
                ["title" => "Operations Manager", "category_id" => 70],             // Management Consultancy
            ],

            "City Waste Management Office – General Santos City" => [
                ["title" => "Waste Management Officer", "category_id" => 38],       // Waste Management
                ["title" => "Field Supervisor", "category_id" => 38],               // Waste Management
                ["title" => "Sanitation Worker", "category_id" => 38],              // Waste Management
                ["title" => "Driver", "category_id" => 52],                         // Transportation Support
                ["title" => "Administrative Staff", "category_id" => 82],           // Office/Admin
                ["title" => "Safety Officer", "category_id" => 20],                 // Membership Organization
                ["title" => "Customer Service Representative", "category_id" => 82],// Office/Admin
                ["title" => "Maintenance Technician", "category_id" => 33],         // Repair and Installation
                ["title" => "IT Support Specialist", "category_id" => 62],          // IT Support
                ["title" => "Operations Supervisor", "category_id" => 70],          // Management Consultancy
            ],

            "Alsons Construction and Development Corporation" => [
                ["title" => "Project Engineer", "category_id" => 41],               // Construction of Buildings
                ["title" => "Site Supervisor", "category_id" => 41],                // Construction of Buildings
                ["title" => "Construction Worker", "category_id" => 41],            // Construction of Buildings
                ["title" => "Safety Officer", "category_id" => 20],                 // Membership Organization
                ["title" => "Administrative Assistant", "category_id" => 82],       // Office/Admin
                ["title" => "Estimator", "category_id" => 41],                      // Construction of Buildings
                ["title" => "Procurement Officer", "category_id" => 82],            // Office/Admin (procurement is business support)
                ["title" => "Equipment Operator", "category_id" => 41],            
                ["title" => "Quality Control Inspector", "category_id" => 71],     
                ["title" => "Project Manager", "category_id" => 70],                // Management Consultancy
            ],

            "Davao Del Sur Construction Corporation" => [
                ["title" => "Project Engineer", "category_id" => 41],
                ["title" => "Site Foreman", "category_id" => 41],
                ["title" => "Construction Laborer", "category_id" => 41],
                ["title" => "Safety Officer", "category_id" => 20],
                ["title" => "Admin Staff", "category_id" => 82],
                ["title" => "Procurement Specialist", "category_id" => 82],
                ["title" => "Equipment Operator", "category_id" => 41],
                ["title" => "Quality Inspector", "category_id" => 71],
                ["title" => "Scheduler", "category_id" => 82],                      // Office/Admin
                ["title" => "Project Manager", "category_id" => 70],
            ],

            "GenSan Builders, Inc." => [
                ["title" => "Site Engineer", "category_id" => 41],
                ["title" => "Foreman", "category_id" => 41],
                ["title" => "Construction Worker", "category_id" => 41],
                ["title" => "Safety Officer", "category_id" => 94],
                ["title" => "Administrative Assistant", "category_id" => 82],
                ["title" => "Procurement Officer", "category_id" => 82],
                ["title" => "Equipment Operator", "category_id" => 41],
                ["title" => "Quality Inspector", "category_id" => 71],
                ["title" => "Project Coordinator", "category_id" => 70],
                ["title" => "Project Manager", "category_id" => 70],
            ],

            "Marbel Construction and Trading" => [
                ["title" => "Construction Engineer", "category_id" => 41],
                ["title" => "Site Supervisor", "category_id" => 41],
                ["title" => "Laborer", "category_id" => 41],
                ["title" => "Safety Officer", "category_id" => 20],
                ["title" => "Admin Staff", "category_id" => 82],
                ["title" => "Procurement Officer", "category_id" => 82],
                ["title" => "Equipment Operator", "category_id" => 41],
                ["title" => "Quality Inspector", "category_id" => 71],
                ["title" => "Scheduler", "category_id" => 82],
                ["title" => "Project Manager", "category_id" => 70],
            ],

            "Sarangani Builders Corporation" => [
                ["title" => "Project Engineer", "category_id" => 41],
                ["title" => "Site Foreman", "category_id" => 41],
                ["title" => "Construction Laborer", "category_id" => 41],
                ["title" => "Safety Officer", "category_id" => 20],
                ["title" => "Admin Assistant", "category_id" => 82],
                ["title" => "Procurement Officer", "category_id" => 82],
                ["title" => "Equipment Operator", "category_id" => 41],
                ["title" => "Quality Inspector", "category_id" => 71],
                ["title" => "Scheduler", "category_id" => 82],
                ["title" => "Project Manager", "category_id" => 70],
            ],

            "Jollibee Foods Corporation - GenSan Branch" => [
                ["title" => "Restaurant Manager", "category_id" => 56],             // Food service
                ["title" => "Crew Member", "category_id" => 56],
                ["title" => "Cashier", "category_id" => 56],
                ["title" => "Cook", "category_id" => 56],
                ["title" => "Delivery Driver", "category_id" => 52],               // Transport support
                ["title" => "Customer Service Representative", "category_id" => 82],
                ["title" => "Janitor", "category_id" => 38],                       // Waste and cleaning
                ["title" => "Maintenance Technician", "category_id" => 33],
                ["title" => "Human Resources Officer", "category_id" => 78],       // Human Resources (if applicable, or 82 office admin)
                ["title" => "Marketing Coordinator", "category_id" => 73],         // Advertising and Marketing
            ],
        ];

        $jobTypes = ['Full-time', 'Part-time', 'Contract', 'Internship', 'Freelance'];
        $experienceLevels = ['Entry-level', 'Intermediate', 'Mid-level', 'Senior/Executive'];
        $workEnvironment = ['Remote', 'On-site', 'Hybrid'];
        $salaryTypes = ['Monthly', 'Hourly', 'Weekly'];

        $skillsList = [
            'Communication', 'Teamwork', 'Problem-solving', 'Time management',
            'Attention to detail', 'Adaptability', 'Leadership', 'Technical skills',
            'Customer service', 'Analytical thinking'
        ];
        
        $companyNameIdMap = Company::pluck('id', 'company_name')->toArray();
        foreach ($companies as $company) {
            $companyLocation = $company['address'] ;

            $companyId = $companyNameIdMap[$company['name']] ?? null;


            // Skip if no matching company record found
            if (!$companyId) {
                continue;
            }

            $hrRecord = HumanResource::with('user')->where('company_id', $companyId)->first();

            if (!$hrRecord || !$hrRecord->user) {
                continue;
            }

            $userId = $hrRecord->user_id;
            $postedByName = $hrRecord->first_name . ' ' . $hrRecord->last_name;


            foreach ($jobTitlesPerCompanyWithDivision[$company['name']] ?? [] as $jobData) {
                $title = $jobData['title'];
                $categoryId = $jobData['category_id'];
                $sectorId = $categoryCategoryMap[$categoryId] ?? Arr::random($sectors); // fallback

                // Randomly pick a year for created_at between 2022 and 2024 for variety
                $postYear = rand(2022, 2024);

                $createdAt = \Carbon\Carbon::create($postYear, rand(1,12), rand(1,28), rand(0,23), rand(0,59), rand(0,59));

                // If job is old (posted before this year), deadline is also in the same year but after createdAt
                if ($postYear < date('Y')) {
                    $jobDeadline = $createdAt->copy()->addDays(rand(10, 90));
                    $status = 'closed';  // old jobs are closed
                } else {
                    // For current year or future postings
                    $jobDeadline = now()->addDays(rand(10, 90));
                    $status = Arr::random(['open', 'closed', 'pending']);
                }
                $salaryType = Arr::random($salaryTypes);

                $isNegotiable = (bool)random_int(0, 1);

                switch ($salaryType) {
                    case 'Hourly':
                        $minSalary = $isNegotiable ? null : random_int(100, 300);
                        $maxSalary = $isNegotiable ? null : random_int($minSalary, $minSalary + 200);
                        break;
                    case 'Weekly':
                        $minSalary = $isNegotiable ? null : random_int(2000, 5000);
                        $maxSalary = $isNegotiable ? null : random_int($minSalary, $minSalary + 7000);
                        break;
                    default: // Monthly
                        $minSalary = $isNegotiable ? null : random_int(5000, 20000);
                        $maxSalary = $isNegotiable ? null : random_int($minSalary, $minSalary + 50000);
                        break;
                }

                $sector = Sector::find($sectorId);
                $category =Category::find($categoryId);

                if ($sector && $category) {
                    $sectorCode = $sector->sector_id;                // e.g., S0001
                    $divisionCodes = $sector->division_codes;        // e.g., 01-03
                    $categoryCode = $category->division_code;        // e.g., 02

                    $initials = collect(explode(' ', $title))
                        ->map(fn ($word) => Str::substr($word, 0, 1))
                        ->implode('');
                    $initials = strtoupper($initials); // e.g., IA for "Insurance Agents"

                    $jobCode = "{$sectorCode}{$divisionCodes}{$initials}-{$categoryCode}";
                } else {
                    $jobCode = null; // fallback if sector or category not found
                }

                $jobCount = Job::count() + 1;
                $jobID = 'JS-' . str_pad($jobCount, 3, '0', STR_PAD_LEFT);
                
                // Compose full job_id
                $fullJobID = "JS-{$jobID}-{$jobCode}";
                
                $job = Job::create([
                    'user_id' => $userId,
                    'company_id' => $companyId,
                    'posted_by' => $postedByName,
                    'job_title' => $title,
                    'sector_id' => $sectorId,
                    'category_id' => $categoryId,
                    'job_employment_type' => Arr::random($jobTypes),
                    'job_work_environment' => Arr::random($workEnvironment),
                    'job_experience_level' => Arr::random($experienceLevels),
                    'job_salary_type' => $salaryType,
                    'is_negotiable' => $isNegotiable,
                    'job_min_salary' => $minSalary,
                    'job_max_salary' => $maxSalary,
                    'job_location' => $companyLocation,
                    'job_vacancies' => random_int(1, 10),
                    'job_description' => "$title position available at our company. Responsibilities include ...",
                    'job_requirements' => "Requirements for $title include ...",
                    'related_skills' => json_encode(array_rand(array_flip($skillsList), 3)),
                    'job_application_limit' => Arr::random([null, 50, 100]),
                    'is_approved' => Arr::random([0, 1]),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,  // or later if you want
                    'job_deadline' => $jobDeadline->format('Y-m-d'),
                    'status' => $status,
                    'job_code' => $jobCode,
                    'job_id' => $fullJobID,
                ]);

            }
        }

    }
}