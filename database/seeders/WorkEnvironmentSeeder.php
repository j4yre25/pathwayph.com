<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class WorkEnvironmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $environments = [
            ['environment_type' => 'On-site'],
            ['environment_type' => 'Remote'],
            ['environment_type' => 'Hybrid'],
        ];

        DB::table('work_environments')->insert($environments);
    }
}
