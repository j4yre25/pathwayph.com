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
        $faker = Faker::create();

        $graduates = Graduate::all();

        foreach ($graduates as $graduate) {
            // Achievement
            if ($graduate->achievements()->count() === 0) {
                $graduate->achievements()->create([
                    'title' => $faker->sentence(3),
                    'issuer' => $faker->company,
                    'date' => $faker->date(),
                    'description' => $faker->paragraph,
                    'type' => $faker->word,
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
                $skillId = rand(1, 10); // Adjust to your skills table
                $graduate->graduateSkills()->create([
                    'skill_id' => $skillId,
                    'proficiency_type' => $faker->randomElement(['Beginner', 'Intermediate', 'Advanced']), // Use allowed values
                    'type' => $faker->word,
                    'years_experience' => rand(1, 5),
                ]);
            }

            // EmploymentPreference
            if (!$graduate->employmentPreference) {
                $graduate->employmentPreference()->create([
                    'job_type' => [$faker->randomElement(['Full-time', 'Part-time'])],
                    'work_environment' => [$faker->randomElement(['Office', 'Remote'])],
                    'location' => [$faker->city, 'Gensan', 'General Santos City'],
                    'additional_notes' => $faker->sentence,
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
                    'employment_type' => $faker->randomElement(['Full-time', 'Part-time']),
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
