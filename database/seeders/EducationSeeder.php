<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EducationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'PhD',         'order_rank' => 1],
            ['id' => 2, 'name' => "Master's",    'order_rank' => 2],
            ['id' => 3, 'name' => "Bachelor's",  'order_rank' => 3],
            ['id' => 4, 'name' => 'Associate',   'order_rank' => 4],
            ['id' => 5, 'name' => 'Vocational',  'order_rank' => 5],
            ['id' => 6, 'name' => 'Senior High', 'order_rank' => 6],
            ['id' => 7, 'name' => 'High School', 'order_rank' => 7],
        ];

        DB::table('educations')->upsert($rows, ['id'], ['name','order_rank']);
    }
}