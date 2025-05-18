<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sectors = [
            ['user_id' => 1, 'sector_id' => 'S0001', 'sector_code' => 'A', 'division_codes' => '01-03', 'name' => 'Agriculture, Forestry, and Fishing'],
            ['user_id' => 1, 'sector_id' => 'S0002', 'sector_code' => 'B', 'division_codes' => '05-09', 'name' => 'Mining and Quarrying'],
            ['user_id' => 1, 'sector_id' => 'S0003', 'sector_code' => 'C', 'division_codes' => '10-33', 'name' => 'Manufacturing'],
            ['user_id' => 1, 'sector_id' => 'S0004', 'sector_code' => 'D', 'division_codes' => '35', 'name' => 'Electricity, Gas, Steam, and Air Conditioning Supply'],
            ['user_id' => 1, 'sector_id' => 'S0005', 'sector_code' => 'E', 'division_codes' => '36-39', 'name' => 'Water Supply, Sewerage, Waste Management, and Remediation Activities'],
            ['user_id' => 1, 'sector_id' => 'S0006', 'sector_code' => 'F', 'division_codes' => '41-43', 'name' => 'Construction'],
            ['user_id' => 1, 'sector_id' => 'S0007', 'sector_code' => 'G', 'division_codes' => '45-47', 'name' => 'Wholesale and Retail Trade; Repair of Motor Vehicles and Motorcycles'],
            ['user_id' => 1, 'sector_id' => 'S0008', 'sector_code' => 'H', 'division_codes' => '49-53', 'name' => 'Transportation and Storage'],
            ['user_id' => 1, 'sector_id' => 'S0009', 'sector_code' => 'I', 'division_codes' => '55-56', 'name' => 'Accommodation and Food Service Activities'],
            ['user_id' => 1, 'sector_id' => 'S0010', 'sector_code' => 'J', 'division_codes' => '58-63', 'name' => 'Information and Communication'],
            ['user_id' => 1, 'sector_id' => 'S0011', 'sector_code' => 'K', 'division_codes' => '64-66', 'name' => 'Financial and Insurance Activities'],
            ['user_id' => 1, 'sector_id' => 'S0012', 'sector_code' => 'L', 'division_codes' => '68', 'name' => 'Real Estate Activities'],
            ['user_id' => 1, 'sector_id' => 'S0013', 'sector_code' => 'M', 'division_codes' => '69-75', 'name' => 'Professional, Scientific, and Technical Services'],
            ['user_id' => 1, 'sector_id' => 'S0014', 'sector_code' => 'N', 'division_codes' => '77-82', 'name' => 'Administrative and Support Service Activities'],
            ['user_id' => 1, 'sector_id' => 'S0015', 'sector_code' => 'O', 'division_codes' => '84', 'name' => 'Public Administration and Defense; Compulsory Social Security'],
            ['user_id' => 1, 'sector_id' => 'S0016', 'sector_code' => 'P', 'division_codes' => '85', 'name' => 'Education'],
            ['user_id' => 1, 'sector_id' => 'S0017', 'sector_code' => 'Q', 'division_codes' => '86-88', 'name' => 'Human Health and Social Work Activities'],
            ['user_id' => 1, 'sector_id' => 'S0018', 'sector_code' => 'R', 'division_codes' => '90-93', 'name' => 'Arts, Entertainment, and Recreation'],
            ['user_id' => 1, 'sector_id' => 'S0019', 'sector_code' => 'S', 'division_codes' => '94-96', 'name' => 'Other Service Activities'],
            ['user_id' => 1, 'sector_id' => 'S0020', 'sector_code' => 'T', 'division_codes' => '98-98', 'name' => 'Activities of Private Households as Employers and Undifferentiated Goods and Services Producing Act'],
            ['user_id' => 1, 'sector_id' => 'S0021', 'sector_code' => 'U', 'division_codes' => '99', 'name' => 'Activities of Extraterritorial Organizations and Bodies'],
        ];

        DB::table('sectors')->insert($sectors);
    }
}
