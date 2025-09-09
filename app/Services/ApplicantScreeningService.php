<?php

namespace App\Services;

use App\Models\Job;
use App\Models\Graduate;
use App\Models\JobType; // ADD

class ApplicantScreeningService
{
    public function screen(Graduate $graduate, Job $job): array
    {
        $labels = [];
        $score = 0;
        $criteria = 0;

        $jobReqs = strtolower(strip_tags((string)$job->job_requirements));
        $jobDesc = strtolower(strip_tags((string)$job->job_description));

        // 1. Skills
        $criteria++;
        $graduateSkills = $graduate->graduateSkills->pluck('skill.name')->filter()->unique()->toArray();
        $skillMatch = false;
        foreach ($graduateSkills as $skill) {
            if (!$skill) continue;
            $s = strtolower($skill);
            if (str_contains($jobReqs, $s) || str_contains($jobDesc, $s)) {
                $skillMatch = true;
                break;
            }
        }
        if ($skillMatch) {
            $score++;
            $labels[] = 'Skills';
        }

        // 2. Education
        $criteria++;
        $education = $graduate->education->first();
        $programName = null;
        if ($education) {
            if (!empty($education->program)) {
                $programName = strtolower($education->program);
            } elseif (method_exists($education, 'programRelation')) {
                $programName = strtolower($education->programRelation?->name ?? '');
            }
        }
        $educationMatch = $programName && (str_contains($jobReqs, $programName) || str_contains($jobDesc, $programName));
        if ($educationMatch) {
            $score++;
            $labels[] = 'Education';
        }

        // 3. Experience
        $criteria++;
        $experienceMatch = false;
        $experienceTitles = $graduate->experience->pluck('job_title')->filter()->unique()->toArray();
        foreach ($experienceTitles as $title) {
            if (!$title) continue;
            $t = strtolower($title);
            if (str_contains($jobReqs, $t) || str_contains($jobDesc, $t)) {
                $experienceMatch = true;
                break;
            }
        }
        if ($experienceMatch) {
            $score++;
            $labels[] = 'Experience';
        }

        // 4. Preferred Job Type (FIX: job.job_type is an ID – resolve to name)
        $criteria++;
        $pref = $graduate->employmentPreference;
        $preferredJobTypes = $pref && $pref->job_type
            ? array_map('trim', explode(',', strtolower($pref->job_type)))
            : [];

        $jobTypeName = null;
        if ($job->relationLoaded('jobTypes') && $job->jobTypes->count()) {
            $jobTypeName = strtolower($job->jobTypes->first()->type);
        } elseif ($job->job_type) {
            $jt = JobType::find($job->job_type);
            $jobTypeName = $jt ? strtolower($jt->type) : null;
        }
        $jobTypeMatch = $jobTypeName && in_array($jobTypeName, $preferredJobTypes);
        if ($jobTypeMatch) {
            $score++;
            $labels[] = 'Preferred Job Type';
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
            $labels[] = 'Preferred Location';
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