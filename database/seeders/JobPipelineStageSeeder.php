<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobPipelineStage;

class JobPipelineStageSeeder extends Seeder
{
    public function run(): void
    {
        if (JobPipelineStage::whereNull('owner_type')->whereNull('owner_id')->exists()) return;

        $defaults = [
            ['Applied','applied'],
            ['Screening','screening'],
            ['Assessment / Exam','assessment'],
            ['Interview','interview'],
            ['Offer','offer'],
            ['Hired','hired'],
            ['Rejected','rejected'],
        ];

        foreach ($defaults as $i => [$name,$slug]) {
            JobPipelineStage::create([
                'owner_type' => null,
                'owner_id'   => null,
                'name'       => $name,
                'slug'       => $slug,
                'position'   => $i + 1,
                'is_terminal'=> in_array($slug,['hired','rejected']),
                'active'     => true,
                'is_default' => true,
            ]);
        }
    }
}