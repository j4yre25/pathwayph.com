<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Salary;

class SalarySeeder extends Seeder
{
    public function run(): void
    {
        $salaryTypes = ['monthly', 'hourly'];
        foreach (range(11, 200) as $i) {
            Salary::create([
                'job_min_salary' => rand(1000, 20000),
                'job_max_salary' => rand(21000, 50000),
                'salary_type' => $salaryTypes[array_rand($salaryTypes)],
            ]);
        }
    }
}