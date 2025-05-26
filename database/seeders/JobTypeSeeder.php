<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            'Full-time',
            'Part-time',
            'Contract',
            'Freelance',
            'Internship',
        ];

        foreach ($types as $type) {
            DB::table('job_types')->updateOrInsert(
                ['type' => $type],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}