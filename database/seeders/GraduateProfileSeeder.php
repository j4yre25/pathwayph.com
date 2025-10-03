<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Graduate;
use Faker\Factory as Faker;

class GraduateProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('en_US'); // Ensure English locale

        $graduates = Graduate::all();

        foreach ($graduates as $graduate) {
            // Achievement
            if ($graduate->achievements()->count() === 0) {
                $graduate->achievements()->create([
                    'title' => $faker->sentence(3),
                    'issuer' => $faker->company,
                    'date' => $faker->date(),
                    'description' => $faker->paragraph,
                    'type' => $faker->randomElement(['Award', 'Recognition']), // Only allowed values
                ]);
            }

            // Testimonial
            if ($graduate->testimonials()->count() === 0) {
                $graduate->testimonials()->create([
                    'author' => $faker->name,
                    'content' => $faker->sentence,
                    'company_name' => $faker->company,
                    'institution_name' => $faker->company,
                ]);
            }

            // GraduateSkill
            if ($graduate->graduateSkills()->count() === 0) {
                $numSkills = rand(4, 10); // Number of skills to add per graduate
                for ($i = 0; $i < $numSkills; $i++) {
                    $skillId = rand(1, 100); // Adjust to your skills table
                    $graduate->graduateSkills()->create([
                        'skill_id' => $skillId,
                        'proficiency_type' => $faker->randomElement(['Beginner', 'Intermediate', 'Advanced']),
                        'type' => $faker->randomElement(['Technical Skills', 'Soft Skills', 'Language Skills', 'Tool/Platform']),
                        'years_experience' => rand(1, 5),
                    ]);
                }
            }

            // EmploymentPreference
            if (!$graduate->employmentPreference) {
                $jobTypes = $faker->randomElements(['Full-time', 'Part-time'], rand(1, 2));
                $workEnvs = $faker->randomElements(['Remote', 'On-site', 'Hybrid'], rand(1, 2));
                $locations = $faker->randomElements([$faker->city, 'Gensan', 'General Santos City'], rand(1, 2));
                $minSalary = $faker->numberBetween(250, 25000);
                $maxSalary = $faker->numberBetween($minSalary + 100, $minSalary + 40000);

                $graduate->employmentPreference()->create([
                    'job_type' => implode(',', $jobTypes),
                    'work_environment' => implode(',', $workEnvs),
                    'location' => implode(',', $locations),
                    'employment_min_salary' => $minSalary,
                    'employment_max_salary' => $maxSalary,
                    'salary_id' => $faker->numberBetween(100, 200), // Adjust as needed
                    'salary_type' => $faker->randomElement(['monthly', 'hourly']),
                    'additional_notes' => $faker->optional()->sentence,
                ]);
            }

            // Certification
            if ($graduate->certifications()->count() === 0) {
                $graduate->certifications()->create([
                    'name' => $faker->word . ' Certification',
                    'issuer' => $faker->company,
                    'issue_date' => $faker->date(),
                    'credential_id' => $faker->uuid,
                ]);
            }

            // Experience
            if ($graduate->experience()->count() === 0) {
                $company = \App\Models\Company::inRandomOrder()->first();
                $companyName = $company ? $company->company_name : $faker->company;

                $graduate->experience()->create([
                    'title' => $faker->jobTitle,
                    'company_name' => $companyName,
                    'description' => $faker->paragraph,
                    'start_date' => $faker->date(),
                    'end_date' => $faker->date(),
                    'employment_type' => $faker->randomElement(['Full-time', 'Part-time', 'Freelance', 'Internship']),
                    'is_current' => $faker->boolean,
                ]);
            }

            // CareerGoal
            if (!$graduate->careerGoals) {
                $graduate->careerGoals()->create([
                    'short_term_goals' => $faker->sentence,
                    'long_term_goals' => $faker->sentence,
                    'industries_of_interest' => $faker->word,
                    'career_path' => $faker->sentence,
                ]);
            }
        }
    }
}
