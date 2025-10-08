<?php

namespace App\Services;

use App\Models\Job;
use App\Models\Graduate;
use Illuminate\Support\Facades\DB;

class ApplicantScreeningService
{
  public function screen(Graduate $graduate, Job $job): array
{
    $labels = [];
    $score = 0;

    // Updated weights from GraduateJobsController
    $weights = [
        'technical_skills' => 7,
        'other_skills' => 2,
        'education' => 5,
        'experience' => 5,
        'job_type' => 0.143,
        'location' => 0.143,
        'work_environment' => 0.143,
        'min_salary' => 0.143,
        'max_salary' => 0.143,
        'salary_type' => 0.143,
        'keywords' => 0.143,
    ];
    $totalWeight = array_sum($weights);

    // Graduate skills: technical and other
    $graduateSkills = \DB::table('graduate_skills')
        ->join('skills', 'graduate_skills.skill_id', '=', 'skills.id')
        ->where('graduate_skills.graduate_id', $graduate->id)
        ->select('skills.name', 'graduate_skills.type')
        ->get();

    $graduateTechnicalSkills = $graduateSkills->where('type', 'Technical Skills')->pluck('name')->all();
    $graduateOtherSkills = $graduateSkills->where('type', '!=', 'Technical Skills')->pluck('name')->all();

    $normalizedGraduateTechnicalSkills = collect($graduateTechnicalSkills)->map(function ($s) {
        $s = trim($s);
        $s = preg_replace('/\s+/', '', $s);
        $s = str_replace(['[', ']', '"', "'"], '', $s);
        return strtolower($s);
    })->unique()->toArray();

    $normalizedGraduateOtherSkills = collect($graduateOtherSkills)->map(function ($s) {
        $s = trim($s);
        $s = preg_replace('/\s+/', '', $s);
        $s = str_replace(['[', ']', '"', "'"], '', $s);
        return strtolower($s);
    })->unique()->toArray();

    // Graduate education (program name)
    $education = \App\Models\GraduateEducation::where('graduate_id', $graduate->id)
        ->orderByDesc('is_current')
        ->orderByDesc('end_date')
        ->first();
    $graduateProgram = $education ? trim(strtolower($education->program)) : null;

    // Graduate experience
    $experiences = \App\Models\Experience::where('graduate_id', $graduate->id)->get();
    $experienceTitles = $experiences->pluck('title')->filter()->unique()->toArray();

    // Employment preferences
    $preferences = \App\Models\EmploymentPreference::where('graduate_id', $graduate->id)->first();
    $preferredJobTypes = $preferences && $preferences->job_type ? explode(',', $preferences->job_type) : [];
    $preferredLocations = $preferences && $preferences->location ? explode(',', $preferences->location) : [];
    $preferredWorkEnvironments = $preferences && $preferences->work_environment ? explode(',', $preferences->work_environment) : [];
    $minSalary = $preferences && $preferences->employment_min_salary ? $preferences->employment_min_salary : null;
    $maxSalary = $preferences && $preferences->employment_max_salary ? $preferences->employment_max_salary : null;
    $salaryType = $preferences && $preferences->salary_type ? $preferences->salary_type : null;

    // Job info
    $skillsArray = [];
    if (is_string($job->skills)) {
        $decoded = json_decode($job->skills, true);
        $skillsArray = is_array($decoded) ? $decoded : [];
    } elseif (is_array($job->skills)) {
        $skillsArray = $job->skills;
    }
    $cleanSkills = array_map(function ($skill) {
        $skill = trim($skill);
        $skill = preg_replace('/\s+/', '', $skill);
        $skill = str_replace(['[', ']', '"', "'"], '', $skill);
        return strtolower($skill);
    }, $skillsArray);
    $normalizedJobSkills = collect($cleanSkills)->unique()->toArray();

    // Job program names
    $jobProgramNames = \DB::table('job_program')
        ->join('programs', 'job_program.program_id', '=', 'programs.id')
        ->where('job_program.job_id', $job->id)
        ->pluck('programs.name')
        ->map(fn($name) => trim(strtolower($name)))
        ->toArray();

    // m1: Technical Skills
    $technicalSkillMatch = 0;
    foreach ($normalizedGraduateTechnicalSkills as $skill) {
        if (in_array($skill, $normalizedJobSkills)) {
            $labels[] = 'Technical Skills';
            $technicalSkillMatch = 1;
            break;
        }
    }
    $score += $technicalSkillMatch * $weights['technical_skills'];

    // m1b: Other Skills (only if no technical skill matched)
    $otherSkillMatch = 0;
    if (!$technicalSkillMatch) {
        foreach ($normalizedGraduateOtherSkills as $skill) {
            if (in_array($skill, $normalizedJobSkills)) {
                $labels[] = 'Other Skills (Soft, Language)';
                $otherSkillMatch = 1;
                break;
            }
        }
    }
    $score += $otherSkillMatch * $weights['other_skills'];

    // m2: Education
    $educationMatch = ($graduateProgram && in_array($graduateProgram, $jobProgramNames)) ? 1 : 0;
    if ($educationMatch) $labels[] = 'Education';
    $score += $educationMatch * $weights['education'];

    // m3: Experience
    $experienceMatch = 0;
    foreach ($experienceTitles as $title) {
        if (stripos($job->job_title, $title) !== false) {
            $labels[] = 'Experience';
            $experienceMatch = 1;
            break;
        }
    }
    $score += $experienceMatch * $weights['experience'];

    // m4: Preferred Job Type
    $jobTypeMatch = in_array($job->job_type, $preferredJobTypes) ? 1 : 0;
    if ($jobTypeMatch) $labels[] = 'Preferred Job Type';
    $score += $jobTypeMatch * $weights['job_type'];

    // m5: Preferred Location
    $locationMatch = in_array($job->location, $preferredLocations) ? 1 : 0;
    if ($locationMatch) $labels[] = 'Preferred Location';
    $score += $locationMatch * $weights['location'];

    // m6: Preferred Work Environment
    $workEnvMatch = in_array($job->work_environment, $preferredWorkEnvironments) ? 1 : 0;
    if ($workEnvMatch) $labels[] = 'Preferred Work Environment';
    $score += $workEnvMatch * $weights['work_environment'];

    // m7: Preferred Min Salary
    $minSalaryMatch = ($minSalary && $job->job_min_salary >= $minSalary) ? 1 : 0;
    if ($minSalaryMatch) $labels[] = 'Preferred Min Salary';
    $score += $minSalaryMatch * $weights['min_salary'];

    // m8: Preferred Max Salary
    $maxSalaryMatch = ($maxSalary && $job->job_max_salary <= $maxSalary) ? 1 : 0;
    if ($maxSalaryMatch) $labels[] = 'Preferred Max Salary';
    $score += $maxSalaryMatch * $weights['max_salary'];

    // m9: Preferred Salary Type
    $salaryTypeMatch = ($salaryType && stripos($job->job_salary_type, $salaryType) !== false) ? 1 : 0;
    if ($salaryTypeMatch) $labels[] = 'Preferred Salary Type';
    $score += $salaryTypeMatch * $weights['salary_type'];

    // m10: Keywords (optional, match in job title or description)
    $keywordsMatch = 0;
    $searchKeywords = $job->search_keywords ?? null;
    if ($searchKeywords) {
        $kw = strtolower($searchKeywords);
        $title = strtolower($job->job_title ?? '');
        $desc = strtolower($job->job_description ?? '');
        if (strpos($title, $kw) !== false || strpos($desc, $kw) !== false) {
            $labels[] = 'Keywords';
            $keywordsMatch = 1;
        }
    }
    $score += $keywordsMatch * $weights['keywords'];

    $match_percentage = $totalWeight > 0 ? round(($score / $totalWeight) * 100) : 0;

    // Screening decision (threshold 60)
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