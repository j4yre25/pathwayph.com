<?php

namespace App\Services;

use App\Models\Job;
use App\Models\User;
use App\Models\Salary;
use App\Models\Location;
use App\Models\Sector;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Str;

class JobCreationService
{
    /**
     * Create a new Job with all related data and relationships.
     *
     * @param array $validated
     * @param User $user
     * @return Job
     */
    public function createJob(array $validated, User $user): Job
    {
        $location = $this->handleLocation($validated);

        $jobData = $this->prepareJobData($validated, $user, null, $location);

    
        [$jobCode, $jobID] = $this->generateJobCodes(
            $validated['sector'] ?? $validated['sector_id'] ?? null,
            $validated['category'] ?? $validated['category_id'] ?? null,
            $user,
            $validated['job_title']
        );
        $jobData['job_code'] = $jobCode;
        $jobData['job_id'] = "JS-{$jobID}-{$jobCode}";

        $job = Job::create($jobData);

        $salary = $this->createSalary($validated, $job->id);

        

        // Sync relationships if present
        if (!empty($validated['job_type'])) {
            $job->jobTypes()->sync(is_array($validated['job_type']) ? $validated['job_type'] : [$validated['job_type']]);
        }
        if ($location) {
            $job->locations()->sync([$location->id]);
        }
        if (!empty($validated['work_environment'])) {
            $job->workEnvironments()->sync(is_array($validated['work_environment']) ? $validated['work_environment'] : [$validated['work_environment']]);
        }
        if (!empty($validated['program_id'])) {
            $job->programs()->attach(is_array($validated['program_id']) ? $validated['program_id'] : [$validated['program_id']]);
        }

        return $job;
    }

    private function createSalary(array $validated, $jobId): Salary
    {
        return Salary::create([
            'job_id' => $jobId,
            'job_min_salary' => !empty($validated['is_negotiable']) && $validated['is_negotiable'] ? null : ($validated['salary']['job_min_salary'] ?? $validated['job_min_salary'] ?? null),
            'job_max_salary' => !empty($validated['is_negotiable']) && $validated['is_negotiable'] ? null : ($validated['salary']['job_max_salary'] ?? $validated['job_max_salary'] ?? null),
            'salary_type' => $validated['salary']['salary_type'] ?? $validated['job_salary_type'] ?? null,
        ]);
    }

    private function handleLocation(array $validated): ?Location
    {
        // work_environment == 2 means remote
        $workEnv = $validated['work_environment'] ?? $validated['work_environment_id'] ?? null;
        if ($workEnv != 2 && !empty($validated['location'])) {
            return Location::firstOrCreate(['address' => $validated['location']]);
        }
        return null;
    }

    private function prepareJobData(array $validated, User $user, ?Salary $salary = null, $location): array
    {
        return [
            'user_id' => $user->id,
            'posted_by' => $user->hr->full_name ?? ($validated['posted_by'] ?? null),
            'company_id' => $user->hr->company_id ?? ($validated['company_id'] ?? null),
            'status' => 'open',

            'job_title' => $validated['job_title'],
            'location' => $location ? $location->id : ($validated['location_id'] ?? null),
            'is_approved' => 1,
            'job_type' => $validated['job_type'] ?? null,
            'job_experience_level' => $validated['job_experience_level'] ?? null,
            'work_environment' => $validated['work_environment'] ?? $validated['work_environment_id'] ?? null,
            'skills' => isset($validated['skills']) ? (is_array($validated['skills']) ? json_encode($validated['skills']) : $validated['skills']) : null,
            'job_vacancies' => $validated['job_vacancies'] ?? null,
            'job_description' => $validated['job_description'] ?? null,
            'job_requirements' => $validated['job_requirements'] ?? null,
            'sector_id' => $validated['sector'] ?? $validated['sector_id'] ?? null,
            'category_id' => $validated['category'] ?? $validated['category_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'job_deadline' => !empty($validated['job_deadline']) ? Carbon::parse($validated['job_deadline'])->format('Y-m-d') : null,
            'job_application_limit' => $validated['job_application_limit'] ?? null,
            'is_negotiable' => $validated['is_negotiable'] ?? null,
            'job_salary_type' => $validated['salary']['salary_type'] ?? $validated['job_salary_type'] ?? null,
            'job_min_salary' => !empty($validated['is_negotiable']) && $validated['is_negotiable'] ? null : ($validated['salary']['job_min_salary'] ?? $validated['job_min_salary'] ?? null),
            'job_max_salary' => !empty($validated['is_negotiable']) && $validated['is_negotiable'] ? null : ($validated['salary']['job_max_salary'] ?? $validated['job_max_salary'] ?? null),
        ];
    }

    private function generateJobCodes($sectorId, $categoryId,User $user, $jobTitle): array
    {
        // $sector = $sectorId ? Sector::find($sectorId) : null;
        // $category = $categoryId ? Category::find($categoryId) : null;
        // $sectorCode = $sector ? $sector->sector_id : 'SEC';
        // $divisionCodes = $sector ? $sector->division_codes : 'DIV';
        // $categoryCode = $category ? $category->division_code : 'CAT';
        // $initials = collect(explode(' ', $jobTitle))
        //     ->map(fn($word) => Str::substr($word, 0, 1))
        //     ->implode('');
        // $initials = strtoupper($initials);
        // $jobCode = "{$sectorCode}{$divisionCodes}{$initials}-{$categoryCode}";
        // $jobCount = Job::count() + 1;
        // $jobID = str_pad($jobCount, 3, '0', STR_PAD_LEFT);
        // return [$jobCode, $jobID];

         $company = $user->company;
        $sector = $company ? $company->sector : null;

        $sectorCode = $sector ? $sector->sector_id : 'SEC';
        $divisionCodes = $sector ? $sector->division_codes : 'DIV';

        $initials = collect(explode(' ', $jobTitle))
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
        $initials = strtoupper($initials);

        $jobCode = "{$sectorCode}{$divisionCodes}{$initials}";
        $jobCount = Job::count() + 1;
        $jobID = str_pad($jobCount, 3, '0', STR_PAD_LEFT);

        return [$jobCode, $jobID];
    }
}