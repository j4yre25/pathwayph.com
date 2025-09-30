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
            if (str_contains($jobReqs, $s) || str_contains($jobDesc, $s) || (is_array($job->skills) && in_array($skill, array_map('strtolower', $job->skills)))) {
                $skillMatch = true;
                break;
            }
        }
        if ($skillMatch) {
            $score++;
            $labels[] = 'Skills';
        }

        // 2. Education (match program/degree to job_requirements or job_description)
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

        // 3. Experience (match job_title to job_requirements or job_description)
        $criteria++;
        $experienceTitles = $graduate->experience->pluck('job_title')->filter()->unique()->toArray();
        $experienceMatch = false;
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

        // 4. Preferred Job Type (match to job_type or jobTypes relation)
        $criteria++;
        $pref = $graduate->employmentPreference;
        $preferredJobTypes = $pref && $pref->job_type
            ? array_map('trim', explode(',', strtolower($pref->job_type)))
            : [];
        $jobTypeNames = [];
        if ($job->relationLoaded('jobTypes') && $job->jobTypes->count()) {
            $jobTypeNames = $job->jobTypes->pluck('type')->map(fn($t) => strtolower($t))->toArray();
        } elseif ($job->job_type) {
            $jt = \App\Models\JobType::find($job->job_type);
            if ($jt) $jobTypeNames = [strtolower($jt->type)];
            else $jobTypeNames = [strtolower($job->job_type)];
        }
        $jobTypeMatch = count(array_intersect($preferredJobTypes, $jobTypeNames)) > 0;
        if ($jobTypeMatch) {
            $score++;
            $labels[] = 'Preferred Job Type';
        }

        // 5. Preferred Location (match to locations relation or job.location)
        $criteria++;
        $preferredLocations = $pref && $pref->location ? array_map('trim', explode(',', strtolower($pref->location))) : [];
        $jobLocations = [];
        if ($job->relationLoaded('locations') && $job->locations->count()) {
            $jobLocations = $job->locations->pluck('address')->map(fn($l) => strtolower($l))->toArray();
        } elseif ($job->location) {
            $jobLocations = [strtolower($job->location)];
        }
        $locationMatch = count(array_intersect($preferredLocations, $jobLocations)) > 0;
        if ($locationMatch) {
            $score++;
            $labels[] = 'Preferred Location';
        }

        // 6. Preferred Work Environment (match to work_environment or workEnvironments relation)
        $criteria++;
        $preferredWorkEnvironments = $pref && $pref->work_environment ? array_map('trim', explode(',', strtolower($pref->work_environment))) : [];
        $jobWorkEnvs = [];
        if ($job->relationLoaded('workEnvironments') && $job->workEnvironments->count()) {
            $jobWorkEnvs = $job->workEnvironments->pluck('type')->map(fn($t) => strtolower($t))->toArray();
        } elseif ($job->work_environment) {
            if (is_array($job->work_environment)) {
                $jobWorkEnvs = array_map('strtolower', $job->work_environment);
            } else {
                $jobWorkEnvs = [strtolower($job->work_environment)];
            }
        }
        $workEnvMatch = count(array_intersect($preferredWorkEnvironments, $jobWorkEnvs)) > 0;
        if ($workEnvMatch) {
            $score++;
            $labels[] = 'Preferred Work Environment';
        }

        // 7. Preferred Min Salary (match to job_min_salary)
        $criteria++;
        $minSalary = $pref && $pref->employment_min_salary ? $pref->employment_min_salary : null;
        $minSalaryMatch = $minSalary && $job->job_min_salary >= $minSalary;
        if ($minSalaryMatch) {
            $score++;
            $labels[] = 'Preferred Min Salary';
        }

        // 8. Preferred Max Salary (match to job_max_salary)
        $criteria++;
        $maxSalary = $pref && $pref->employment_max_salary ? $pref->employment_max_salary : null;
        $maxSalaryMatch = $maxSalary && $job->job_max_salary <= $maxSalary;
        if ($maxSalaryMatch) {
            $score++;
            $labels[] = 'Preferred Max Salary';
        }

        // 9. Preferred Salary Type (match to job_salary_type)
        $criteria++;
        $salaryType = $pref && $pref->salary_type ? strtolower($pref->salary_type) : null;
        $salaryTypeMatch = $salaryType && stripos(strtolower($job->job_salary_type), $salaryType) !== false;
        if ($salaryTypeMatch) {
            $score++;
            $labels[] = 'Preferred Salary Type';
        }

        // Calculate match percentage
        $matchPercentage = $criteria > 0 ? round(($score / $criteria) * 100) : 0;

        // Screening decision (use a constant threshold, e.g. 60)
        $threshold = 60;
        if ($matchPercentage >= $threshold) {
            $screening_label = 'Shortlisted';
            $screening_feedback = 'Auto-screened: High match (' . $matchPercentage . '%)';
            $is_shortlisted = true;
            $status = 'screening';
        } else {
            $screening_label = 'Review Further';
            $screening_feedback = 'Auto-screened: Match (' . $matchPercentage . '%)';
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