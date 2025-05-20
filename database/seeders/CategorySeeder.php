<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $user_id = 1;  // or get dynamically

        $categories = [
            // 1 - A - AGRICULTURE, FORESTRY, AND FISHING
            ['id' => '01', 'name' => 'Crop and animal production, hunting and related service activities', 'sector_id' => '1', 'division_code' => '01'],
            ['id' => '02', 'name' => 'Forestry and Logging', 'sector_id' => '1', 'division_code' => '02'],
            ['id' => '03', 'name' => 'Fishing and Aquaculture', 'sector_id' => '1', 'division_code' => '03'],

            // 2 - B - Mining and Quarrying
            ['id' => '05', 'name' => 'Mining of Coal and Lignite', 'sector_id' => '2', 'division_code' => '05'],
            ['id' => '06', 'name' => 'Extraction of Crude Petroleum and Natural Gas', 'sector_id' => '2', 'division_code' => '06'],
            ['id' => '07', 'name' => 'Mining of Metal Ores', 'sector_id' => '2', 'division_code' => '07'],
            ['id' => '08', 'name' => 'Other Mining and Quarrying', 'sector_id' => '2', 'division_code' => '08'],
            ['id' => '09', 'name' => 'Mining Support Service Activities', 'sector_id' => '2', 'division_code' => '09'],

            // 3 - C - Manufacturing
            ['id' => '10', 'name' => 'Manufacture of Food Products', 'sector_id' => '3', 'division_code' => '10'],
            ['id' => '11', 'name' => 'Manufacture of Beverages', 'sector_id' => '3', 'division_code' => '11'],
            ['id' => '12', 'name' => 'Manufacture of Tobacco Products', 'sector_id' => '3', 'division_code' => '12'],
            ['id' => '13', 'name' => 'Manufacture of Textiles', 'sector_id' => '3', 'division_code' => '13'],
            ['id' => '14', 'name' => 'Manufacture of Wearing Apparel', 'sector_id' => '3', 'division_code' => '14'],
            ['id' => '15', 'name' => 'Manufacture of Leather and Related Products', 'sector_id' => '3', 'division_code' => '15'],
            ['id' => '16', 'name' => 'Manufacture of Wood and Of Products of Wood and Cork, Except Furniture; Manufacture of Articles of Bamboo, Cane, Rattan and the Like; Manufacture of Articles of Straw and Plating Materials', 'sector_id' => '3', 'division_code' => '16'],
            ['id' => '17', 'name' => 'Manufacture of Paper and Paper Products', 'sector_id' => '3', 'division_code' => '17'],
            ['id' => '18', 'name' => 'Printing and Reproduction of Recorded Media', 'sector_id' => '3', 'division_code' => '18'],
            ['id' => '19', 'name' => 'Manufacture of Coke and Refined Petroleum Products', 'sector_id' => '3', 'division_code' => '19'],
            ['id' => '20', 'name' => 'Manufacture of Chemicals and Chemical Products', 'sector_id' => '3', 'division_code' => '20'],
            ['id' => '21', 'name' => 'Manufacture of Basic Pharmaceutical Products and Pharmaceutical Preparations', 'sector_id' => '3', 'division_code' => '21'],
            ['id' => '22', 'name' => 'Manufacture of Rubber and Plastic Products', 'sector_id' => '3', 'division_code' => '22'],
            ['id' => '23', 'name' => 'Manufacture of Other Non-Metallic Mineral Products', 'sector_id' => '3', 'division_code' => '23'],
            ['id' => '24', 'name' => 'Manufacture of Basic Metals', 'sector_id' => '3', 'division_code' => '24'],
            ['id' => '25', 'name' => 'Manufacture of Fabricated Metal Products, Except Machinery and Equipment', 'sector_id' => '3', 'division_code' => '25'],
            ['id' => '26', 'name' => 'Manufacture of Computer, Electronic, and Optical Products', 'sector_id' => '3', 'division_code' => '26'],
            ['id' => '27', 'name' => 'Manufacture of Electrical Equipment', 'sector_id' => '3', 'division_code' => '27'],
            ['id' => '28', 'name' => 'Manufacture of Machinery and Equipment, N.E.C.', 'sector_id' => '3', 'division_code' => '28'],
            ['id' => '29', 'name' => 'Manufacture of Motor Vehicles, Trailers, and Semi-trailers', 'sector_id' => '3', 'division_code' => '29'],
            ['id' => '30', 'name' => 'Manufacture of Other Transport Equipment', 'sector_id' => '3', 'division_code' => '30'],
            ['id' => '31', 'name' => 'Manufacture of Furniture', 'sector_id' => '3', 'division_code' => '31'],
            ['id' => '32', 'name' => 'Other Manufacturing', 'sector_id' => '3', 'division_code' => '32'],
            ['id' => '33', 'name' => 'Repair and Installation of Machinery and Equipment', 'sector_id' => '3', 'division_code' => '33'],

            // 4 - D - Electricity, Gas, Steam, and Air Conditioning Supply
            ['id' => '35', 'name' => 'Electricity, Gas, Steam, and Air Conditioning Supply', 'sector_id' => '4', 'division_code' => '35'],

            // 5 - E - Water Supply, Sewerage, Waste Management, and Remediation Activities
            ['id' => '36', 'name' => 'Water collection, treatment, and supply', 'sector_id' => '5', 'division_code' => '36'],
            ['id' => '37', 'name' => 'Sewerage', 'sector_id' => '5', 'division_code' => '37'],
            ['id' => '38', 'name' => 'Waste Collection Treatments and Disposal Activities, Materials Recovery', 'sector_id' => '5', 'division_code' => '38'],
            ['id' => '39', 'name' => 'Remediation Activities and Other Waste Management Service', 'sector_id' => '5', 'division_code' => '39'],

            // 6 - F - Construction
            ['id' => '41', 'name' => 'Construction of Buildings', 'sector_id' => '6', 'division_code' => '41'],
            ['id' => '42', 'name' => 'Civil Engineering', 'sector_id' => '6', 'division_code' => '42'],
            ['id' => '43', 'name' => 'Specialized Construction Activities', 'sector_id' => '6', 'division_code' => '43'],

            // 7 - G - Wholesale and Retail Trade; Repair of Motor Vehicles and Motorcycles
            ['id' => '45', 'name' => 'Wholesale and Retail Trade and Repair of Motor Vehicles and Motorcycles', 'sector_id' => '7', 'division_code' => '45'],
            ['id' => '46', 'name' => 'Wholesale Trade, Except of Motor Vehicles and Motorcycles', 'sector_id' => '7', 'division_code' => '46'],
            ['id' => '47', 'name' => 'Retail Trade, Except of Motor Vehicles and Motorcycles', 'sector_id' => '7', 'division_code' => '47'],

            // 8 - H - Transportation and Storage
            ['id' => '49', 'name' => 'Land Transport and Transport via Pipelines', 'sector_id' => '8', 'division_code' => '49'],
            ['id' => '50', 'name' => 'Water Transport', 'sector_id' => '8', 'division_code' => '50'],
            ['id' => '51', 'name' => 'Air Transport', 'sector_id' => '8', 'division_code' => '51'],
            ['id' => '52', 'name' => 'Warehousing and Support Activities for Transportation', 'sector_id' => '8', 'division_code' => '52'],

            // 9 - I - Accommodation and Food Service Activities
            ['id' => '55', 'name' => 'Accommodation', 'sector_id' => '9', 'division_code' => '55'],
            ['id' => '56', 'name' => 'Food and Beverage Service Activities', 'sector_id' => '9', 'division_code' => '56'],

            // 10 - J - Information and Communication
            ['id' => '58', 'name' => 'Publishing Activities', 'sector_id' => '10', 'division_code' => '58'],
            ['id' => '59', 'name' => 'Motion Picture, Video and Television Program Production, Sound Recording and Music Publishing Activities', 'sector_id' => '10', 'division_code' => '59'],
            ['id' => '60', 'name' => 'Programming and Broadcasting Activities', 'sector_id' => '10', 'division_code' => '60'],
            ['id' => '61', 'name' => 'Telecommunications', 'sector_id' => '10', 'division_code' => '61'],
            ['id' => '62', 'name' => 'Information Service Activities', 'sector_id' => '10', 'division_code' => '62'],

            // 11 - K - Financial and Insurance Activities
            ['id' => '64', 'name' => 'Financial Service Activities, Except Insurance and Pension Funding', 'sector_id' => '11', 'division_code' => '64'],
            ['id' => '65', 'name' => 'Insurance, Reinsurance and Pension Funding, Except Compulsory Social Security', 'sector_id' => '11', 'division_code' => '65'],
            ['id' => '66', 'name' => 'Activities Auxiliary to Financial Service and Insurance Activities', 'sector_id' => '11', 'division_code' => '66'],

            // 12 - L - Real Estate Activities
            ['id' => '68', 'name' => 'Real Estate Activities', 'sector_id' => '12', 'division_code' => '68'],

            // 13 - M - Professional, Scientific and Technical Activities
            ['id' => '69', 'name' => 'Legal and Accounting Activities', 'sector_id' => '13', 'division_code' => '69'],
            ['id' => '70', 'name' => 'Activities of Head Offices; Management Consultancy Activities', 'sector_id' => '13', 'division_code' => '70'],
            ['id' => '71', 'name' => 'Architectural and Engineering Activities; Technical Testing and Analysis', 'sector_id' => '13', 'division_code' => '71'],
            ['id' => '72', 'name' => 'Scientific Research and Development', 'sector_id' => '13', 'division_code' => '72'],
            ['id' => '73', 'name' => 'Advertising and Market Research', 'sector_id' => '13', 'division_code' => '73'],
            ['id' => '74', 'name' => 'Other Professional, Scientific and Technical Activities', 'sector_id' => '13', 'division_code' => '74'],
            ['id' => '75', 'name' => 'Veterinary Activities', 'sector_id' => '13', 'division_code' => '75'],

            // 14 - N - Administrative and Support Service Activities
            ['id' => '77', 'name' => 'Rental and Leasing Activities', 'sector_id' => '14', 'division_code' => '77'],
            ['id' => '78', 'name' => 'Employment Activities', 'sector_id' => '14', 'division_code' => '78'],
            ['id' => '79', 'name' => 'Travel Agency, Tour Operator and Other Reservation Service and Related Activities', 'sector_id' => '14', 'division_code' => '79'],
            ['id' => '80', 'name' => 'Security and Investigation Activities', 'sector_id' => '14', 'division_code' => '80'],
            ['id' => '81', 'name' => 'Services to Buildings and Landscape Activities', 'sector_id' => '14', 'division_code' => '81'],
            ['id' => '82', 'name' => 'Office Administrative, Office Support and Other Business Support Activities', 'sector_id' => '14', 'division_code' => '82'],

            // 15 - O - Public Administration and Defence; Compulsory Social Security
            ['id' => '84', 'name' => 'Public Administration and Defence; Compulsory Social Security', 'sector_id' => '15', 'division_code' => '84'],

            // 16 - P - Education
            ['id' => '85', 'name' => 'Education', 'sector_id' => '16', 'division_code' => '85'],

            // 17 - Q - Human Health and Social Work Activities
            ['id' => '86', 'name' => 'Human Health Activities', 'sector_id' => '17', 'division_code' => '86'],
            ['id' => '87', 'name' => 'Social Work Activities Without Accommodation', 'sector_id' => '17', 'division_code' => '87'],

            // 18 - R - Arts, Entertainment and Recreation
            ['id' => '90', 'name' => 'Creative, Arts and Entertainment Activities', 'sector_id' => '18', 'division_code' => '90'],
            ['id' => '91', 'name' => 'Libraries, Archives, Museums and Other Cultural Activities', 'sector_id' => '18', 'division_code' => '91'],
            ['id' => '92', 'name' => 'Gambling and Betting Activities', 'sector_id' => '18', 'division_code' => '92'],
            ['id' => '93', 'name' => 'Sports Activities and Amusement and Recreation Activities', 'sector_id' => '18', 'division_code' => '93'],

            // 19 - S - Other Service Activities
            ['id' => '94', 'name' => 'Activities of Membership Organizations', 'sector_id' => '19', 'division_code' => '94'],
            ['id' => '95', 'name' => 'Repair of Computers and Personal and Household Goods', 'sector_id' => '19', 'division_code' => '95'],
            ['id' => '96', 'name' => 'Other Personal Service Activities', 'sector_id' => '19', 'division_code' => '96'],

            // 20 - T - Activities of Households as Employers; Undifferentiated Goods- and Services-Producing Activities of Households for Own Use
            ['id' => '97', 'name' => 'Activities of Households as Employers of Domestic Personnel', 'sector_id' => '20', 'division_code' => '97'],
            ['id' => '98', 'name' => 'Undifferentiated Goods- and Services-Producing Activities of Private Households for Own Use', 'sector_id' => '20', 'division_code' => '98'],

            // 21 - U - Activities of Extraterritorial Organizations and Bodies
            ['id' => '99', 'name' => 'Activities of Extraterritorial Organizations and Bodies', 'sector_id' => '21', 'division_code' => '99'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'id' => $category['id'],
                'name' => $category['name'],
                'sector_id' => $category['sector_id'],
                'division_code' => $category['division_code'],
                'user_id' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

