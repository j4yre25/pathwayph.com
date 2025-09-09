<?php

namespace App\Services;

use App\Models\Job;
use App\Models\Graduate;
use App\Models\EmploymentPreference;

class ApplicantScreeningService
{
    public function screen(Graduate $graduate, Job $job)
    {
        $labels = [];
        $score = 0;
        $criteria = 0;

        // 1. Skills
        $criteria++;
        $graduateSkills = $graduate->graduateSkills->pluck('skill.name')->filter()->unique()->toArray();
        $skillMatch = false;
        foreach ($graduateSkills as $skill) {
            if (stripos(json_encode($job->skills), $skill) !== false) {
                $labels[] = 'Skills';
                $skillMatch = true;
                break;
            }
        }
        if ($skillMatch) $score++;

        // 2. Education
        $criteria++;
        $education = $graduate->education->first();
        // Support either raw string in 'program' column or related Program model via programRelation()
        $programName = null;
        if ($education) {
            if (is_string($education->program)) {
                $programName = $education->program;
            } elseif (method_exists($education, 'programRelation') && $education->relationLoaded('programRelation')) {
                $programName = $education->programRelation?->name;
            } elseif (method_exists($education, 'programRelation')) {
                $programName = $education->programRelation()->value('name');
            }
        }
        $educationMatch = $programName && stripos($job->job_requirements, $programName) !== false;
        if ($educationMatch) {
            $labels[] = 'Education';
            $score++;
        }

        // 3. Experience
        $criteria++;
        $experiences = $graduate->experience;
        $experienceTitles = $experiences->pluck('job_title')->filter()->unique()->toArray();
        $experienceMatch = false;
        foreach ($experienceTitles as $title) {
            if (stripos($job->job_title, $title) !== false) {
                $labels[] = 'Experience';
                $experienceMatch = true;
                break;
            }
        }
        if ($experienceMatch) $score++;

        // 4. Preferred Job Type
        $criteria++;
        $pref = $graduate->employmentPreference;
        $preferredJobTypes = $pref && $pref->job_type ? explode(',', $pref->job_type) : [];
        $jobTypeMatch = $job->job_type && in_array($job->job_type, $preferredJobTypes);
        if ($jobTypeMatch) {
            $labels[] = 'Preferred Job Type';
            $score++;
        }

        // 5. Preferred Location
        $criteria++;
        $preferredLocations = $pref && $pref->location ? explode(',', $pref->location) : [];
        $locationMatch = $job->location && in_array($job->location, $preferredLocations);
        if ($locationMatch) {
            $labels[] = 'Preferred Location';
            $score++;
        }

        // 6. Preferred Work Environment
        $criteria++;
        $preferredWorkEnvironments = $pref && $pref->work_environment ? explode(',', $pref->work_environment) : [];
        $workEnvMatch = $job->work_environment && in_array($job->work_environment, $preferredWorkEnvironments);
        if ($workEnvMatch) {
            $labels[] = 'Preferred Work Environment';
            $score++;
        }

        // 7. Preferred Min Salary
        $criteria++;
        $minSalary = $pref && $pref->employment_min_salary ? $pref->employment_min_salary : null;
        $minSalaryMatch = $minSalary && $job->job_min_salary >= $minSalary;
        if ($minSalaryMatch) {
            $labels[] = 'Preferred Min Salary';
            $score++;
        }

        // 8. Preferred Max Salary
        $criteria++;
        $maxSalary = $pref && $pref->employment_max_salary ? $pref->employment_max_salary : null;
        $maxSalaryMatch = $maxSalary && $job->job_max_salary <= $maxSalary;
        if ($maxSalaryMatch) {
            $labels[] = 'Preferred Max Salary';
            $score++;
        }

        // 9. Preferred Salary Type
        $criteria++;
        $salaryType = $pref && $pref->salary_type ? $pref->salary_type : null;
        $salaryTypeMatch = $salaryType && stripos($job->job_salary_type, $salaryType) !== false;
        if ($salaryTypeMatch) {
            $labels[] = 'Preferred Salary Type';
            $score++;
        }

        // Calculate match percentage
        $matchPercentage = $criteria > 0 ? round(($score / $criteria) * 100) : 0;

        // Screening decision
        if ($matchPercentage >= 70) {
            $screening_label = 'Shortlisted';
            $screening_feedback = 'Auto-screened: High match (' . $matchPercentage . '%)';
            $is_shortlisted = true;
            $status = 'shortlisted';
        } elseif ($matchPercentage >= 30) {
            $screening_label = 'Review Further';
            $screening_feedback = 'Auto-screened: Medium match (' . $matchPercentage . '%)';
            $is_shortlisted = false;
            $status = 'applied';
        } else {
            $screening_label = 'Not Recommended';
            $screening_feedback = 'Auto-screened: Low match (' . $matchPercentage . '%)';
            $is_shortlisted = false;
            $status = 'applied';
        }

        return [
            'labels' => $labels,
            'score' => $score,
            'criteria' => $criteria,
            'match_percentage' => $matchPercentage,
            'screening_label' => $screening_label,
            'screening_feedback' => $screening_feedback,
            'is_shortlisted' => $is_shortlisted,
            'status' => $status,
        ];
    }
}