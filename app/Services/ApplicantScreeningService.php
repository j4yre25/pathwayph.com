<?php

namespace App\Services;

use App\Models\Job;
use App\Models\Graduate;

class ApplicantScreeningService
{
    public function screen(Graduate $graduate, Job $job): array
    {
        $labels = [];
        $score = 0;

        // Weights for each criterion
        $weights = [
            'skills' => 3,
            'education' => 2,
            'experience' => 2,
            'job_type' => 1,
            'location' => 1,
            'work_environment' => 1,
            'min_salary' => 1,
            'max_salary' => 1,
            'salary_type' => 1,
            'keywords' => 2,
        ];
        $totalWeight = array_sum($weights);

        // Graduate info
        $graduateSkills = $graduate->graduateSkills->pluck('skill.name')->filter()->unique()->toArray();
        $education = $graduate->education->first();
        $program = $education ? $education->program : null;
        $experiences = $graduate->experience->pluck('job_title')->filter()->unique()->toArray();
        $preferences = $graduate->employmentPreference;
        $preferredJobTypes = [];
        if ($preferences && $preferences->job_type) {
            if (is_string($preferences->job_type)) {
                $preferredJobTypes = explode(',', $preferences->job_type);
            } elseif (is_array($preferences->job_type)) {
                $preferredJobTypes = $preferences->job_type;
            } else {
                $preferredJobTypes = [];
            }
        }
        $preferredLocations = [];
        if ($preferences && $preferences->location) {
            if (is_string($preferences->location)) {
                $preferredLocations = explode(',', $preferences->location);
            } elseif (is_array($preferences->location)) {
                $preferredLocations = $preferences->location;
            }
        }
        $preferredWorkEnvironments = [];
        if ($preferences && $preferences->work_environment) {
            if (is_string($preferences->work_environment)) {
                $preferredWorkEnvironments = explode(',', $preferences->work_environment);
            } elseif (is_array($preferences->work_environment)) {
                $preferredWorkEnvironments = $preferences->work_environment;
            }
        }
        $minSalary = $preferences && $preferences->employment_min_salary ? $preferences->employment_min_salary : null;
        $maxSalary = $preferences && $preferences->employment_max_salary ? $preferences->employment_max_salary : null;
        $salaryType = $preferences && $preferences->salary_type ? $preferences->salary_type : null;

        // Job info
        if (is_array($job->skills)) {
            $jobSkills = $job->skills;
        } elseif (is_string($job->skills)) {
            $jobSkills = json_decode($job->skills, true) ?: [];
        } else {
            $jobSkills = [];
        }
        $jobType = $job->job_type;
        $jobTypeNames = [];
        if ($job->relationLoaded('jobTypes') && $job->jobTypes && $job->jobTypes->count()) {
            $jobTypeNames = $job->jobTypes->pluck('type')->filter()->values()->all();
        } elseif (is_array($jobType)) {
            $jobTypeNames = $jobType;
        } elseif (is_string($jobType) && $jobType !== '') {
            $jobTypeNames = [$jobType];
        }
        $jobLocations = $job->relationLoaded('locations') && $job->locations ? $job->locations->pluck('address')->filter()->values()->all() : [];
        $jobWorkEnvironment = $job->work_environment;
        $jobMinSalary = $job->salary->job_min_salary ?? null;
        $jobMaxSalary = $job->salary->job_max_salary ?? null;
        $jobSalaryType = $job->salary->salary_type ?? null;

        // m1: Skills
        $skillMatch = 0;
        foreach ($graduateSkills as $skill) {
            if (stripos(json_encode($jobSkills), $skill) !== false) {
                $labels[] = 'Skills';
                $skillMatch = 1;
                break;
            }
        }
        $score += $skillMatch * $weights['skills'];

        // m2: Education
        $educationMatch = ($program && stripos($job->job_requirements, $program) !== false) ? 1 : 0;
        if ($educationMatch) $labels[] = 'Education';
        $score += $educationMatch * $weights['education'];

        // m3: Experience
        $experienceMatch = 0;
        foreach ($experiences as $title) {
            if (stripos($job->job_title, $title) !== false) {
                $labels[] = 'Experience';
                $experienceMatch = 1;
                break;
            }
        }
        $score += $experienceMatch * $weights['experience'];

        // m4: Preferred Job Type
        $jobTypeMatch = count(array_intersect($preferredJobTypes, $jobTypeNames)) > 0 ? 1 : 0;
        if ($jobTypeMatch) $labels[] = 'Preferred Job Type';
        $score += $jobTypeMatch * $weights['job_type'];

        // m5: Preferred Location
        $locationMatch = count(array_intersect($preferredLocations, $jobLocations)) > 0 ? 1 : 0;
        if ($locationMatch) $labels[] = 'Preferred Location';
        $score += $locationMatch * $weights['location'];

        // m6: Preferred Work Environment
        $workEnvMatch = in_array($jobWorkEnvironment, $preferredWorkEnvironments) ? 1 : 0;
        if ($workEnvMatch) $labels[] = 'Preferred Work Environment';
        $score += $workEnvMatch * $weights['work_environment'];

        // m7: Preferred Min Salary
        $minSalaryMatch = ($minSalary && $jobMinSalary >= $minSalary) ? 1 : 0;
        if ($minSalaryMatch) $labels[] = 'Preferred Min Salary';
        $score += $minSalaryMatch * $weights['min_salary'];

        // m8: Preferred Max Salary
        $maxSalaryMatch = ($maxSalary && $jobMaxSalary <= $maxSalary) ? 1 : 0;
        if ($maxSalaryMatch) $labels[] = 'Preferred Max Salary';
        $score += $maxSalaryMatch * $weights['max_salary'];

        // m9: Preferred Salary Type
        $salaryTypeMatch = ($salaryType && stripos($jobSalaryType, $salaryType) !== false) ? 1 : 0;
        if ($salaryTypeMatch) $labels[] = 'Preferred Salary Type';
        $score += $salaryTypeMatch * $weights['salary_type'];

        // m10: Keywords (optional, not used in screening unless you want to pass it in)
        // $keywordsMatch = 0;
        // $score += $keywordsMatch * $weights['keywords'];

        // Only include jobs with at least one label (i.e., a match)
        $match_percentage = $totalWeight > 0 ? round(($score / $totalWeight) * 100) : 0;

        // Screening decision (use a constant threshold, e.g. 60)
        $threshold = 60;
        if ($match_percentage >= $threshold) {
            $screening_label = 'Shortlisted';
            $screening_feedback = 'Auto-screened: High match (' . $match_percentage . '%)';
            $is_shortlisted = true;
            $status = 'screening';
        } else {
            $screening_label = 'Review Further';
            $screening_feedback = 'Auto-screened: Match (' . $match_percentage . '%)';
            $is_shortlisted = false;
            $status = 'applied';
        }

        return [
            'labels' => array_unique($labels),
            'score' => $score,
            'match_percentage' => $match_percentage,
            'screening_label' => $screening_label,
            'screening_feedback' => $screening_feedback,
            'is_shortlisted' => $is_shortlisted,
            'status' => $status,
        ];
    }
}