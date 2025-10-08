<?php

namespace App\Services;

use App\Models\Job;
use App\Models\Graduate;
use Illuminate\Support\Facades\DB;

class ApplicantScreeningService
{
    /**
     * Screen a single job against a graduate using the same logic as GraduateJobsController::recommendations()
     */
    public function screen(Graduate $graduate, Job $job): array
    {
        $labels = [];
        $score  = 0;

        // Weights (same distribution concept as recommendations(); keep consistent)
        $weights = [
            'technical_skills' => 7,
            'other_skills'     => 2,
            'education'        => 5,
            'experience'       => 5,
            'job_type'         => 0.143,
            'location'         => 0.143,
            'work_environment' => 0.143,
            'min_salary'       => 0.143,
            'max_salary'       => 0.143,
            'salary_type'      => 0.143,
            'keywords'         => 0.143,
        ];
        $totalWeight = array_sum($weights);

        /* --------- Gather Graduate Context --------- */
        // Skills (distinguish technical vs others via type column if available)
        $gradSkillModels = $graduate->graduateSkills()
            ->with('skill:id,name')
            ->get()
            ->map(fn($gs) => [
                'name' => $gs->skill?->name,
                'type' => $gs->type
            ])
            ->filter(fn($r) => !empty($r['name']));

        $graduateTechnicalSkills = $gradSkillModels
            ->where('type', 'Technical Skills')
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        $graduateOtherSkills = $gradSkillModels
            ->reject(fn($r) => $r['type'] === 'Technical Skills')
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        $normalizeSkill = function ($s) {
            $s = trim($s);
            $s = preg_replace('/\s+/', '', $s);
            $s = str_replace(['[',']','"',"'"], '', $s);
            return strtolower($s);
        };

        $normalizedGraduateTechnicalSkills = collect($graduateTechnicalSkills)->map($normalizeSkill)->unique()->values()->all();
        $normalizedGraduateOtherSkills     = collect($graduateOtherSkills)->map($normalizeSkill)->unique()->values()->all();

        // Latest / current education (program)
        $education = $graduate->education()
            ->orderByDesc('is_current')
            ->orderByDesc('end_date')
            ->first();
        $graduateProgram = $education ? trim(strtolower($education->program)) : null;

        // Experiences (titles)
        $experienceTitles = $graduate->experience()
            ->pluck('title')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Preferences
        $preferences = $graduate->employmentPreference;

        $parsePrefArray = function ($value) {
            if (is_array($value)) return array_values(array_filter(array_map('trim', $value)));
            if (is_string($value)) {
                $dec = @json_decode($value, true);
                if (is_array($dec)) {
                    return array_values(array_filter(array_map('trim', $dec)));
                }
                return array_values(array_filter(array_map('trim', explode(',', $value))));
            }
            return [];
        };

        $preferredJobTypes         = $preferences ? $parsePrefArray($preferences->job_type) : [];
        $preferredLocations        = $preferences ? $parsePrefArray($preferences->location) : [];
        $preferredWorkEnvironments = $preferences ? $parsePrefArray($preferences->work_environment) : [];
        $minSalaryPref             = $preferences->employment_min_salary ?? null;
        $maxSalaryPref             = $preferences->employment_max_salary ?? null;
        $salaryTypePref            = $preferences->salary_type ?? null;

        /* --------- Gather Job Context --------- */
        // Job skills
        if (is_array($job->skills)) {
            $jobSkillsRaw = $job->skills;
        } elseif (is_string($job->skills)) {
            $jobSkillsRaw = json_decode($job->skills, true) ?: [];
        } else {
            $jobSkillsRaw = [];
        }

        $normalizedJobSkills = collect($jobSkillsRaw)
            ->map($normalizeSkill)
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Program names linked via pivot job_program (if exists)
        $jobProgramNames = [];
        if (DB::getSchemaBuilder()->hasTable('job_program')) {
            $jobProgramNames = DB::table('job_program')
                ->join('programs', 'job_program.program_id', '=', 'programs.id')
                ->where('job_program.job_id', $job->id)
                ->pluck('programs.name')
                ->map(fn($n) => trim(strtolower($n)))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        // Job type list (from relation or fallback)
        $jobTypeNames = [];
        if ($job->relationLoaded('jobTypes') && $job->jobTypes && $job->jobTypes->count()) {
            $jobTypeNames = $job->jobTypes->pluck('type')->filter()->values()->all();
        } elseif (is_array($job->job_type)) {
            $jobTypeNames = $job->job_type;
        } elseif (is_string($job->job_type) && $job->job_type !== '') {
            $jobTypeNames = [$job->job_type];
        }

        // Job location(s) as array for comparison (split commas)
        $jobLocations = [];
        if (!empty($job->location)) {
            if (is_string($job->location)) {
                $jobLocations = array_map('trim', explode(',', $job->location));
            } elseif (is_array($job->location)) {
                $jobLocations = array_map('trim', $job->location);
            }
        }

        // Work environment (string / csv / json -> array)
        $jobWorkEnvRaw = $job->work_environment;
        if (is_string($jobWorkEnvRaw)) {
            $decoded = json_decode($jobWorkEnvRaw, true);
            if (is_array($decoded)) {
                $jobWorkEnvironment = array_values(array_filter(array_map('trim', $decoded)));
            } else {
                $jobWorkEnvironment = array_values(array_filter(array_map('trim', explode(',', $jobWorkEnvRaw))));
            }
        } elseif (is_array($jobWorkEnvRaw)) {
            $jobWorkEnvironment = array_values(array_filter(array_map('trim', $jobWorkEnvRaw)));
        } elseif ($jobWorkEnvRaw) {
            $jobWorkEnvironment = [trim($jobWorkEnvRaw)];
        } else {
            $jobWorkEnvironment = [];
        }

        // Salary (relation or direct columns)
        $jobMinSalary   = $job->salary->job_min_salary ?? $job->job_min_salary ?? null;
        $jobMaxSalary   = $job->salary->job_max_salary ?? $job->job_max_salary ?? null;
        $jobSalaryType  = $job->salary->salary_type     ?? $job->job_salary_type ?? null;
        $searchKeywords = $job->search_keywords ?? null;

        // Normalized for location compare
        $normPreferredLocations = array_map('mb_strtolower', array_map('trim', $preferredLocations));
        $normJobLocations       = array_map('mb_strtolower', array_map('trim', $jobLocations));

        /* --------- Scoring (mirrors recommendations()) --------- */

        // m1 Technical Skills
        $technicalSkillMatch = (int)collect($normalizedGraduateTechnicalSkills)
            ->contains(fn($gs) => in_array($gs, $normalizedJobSkills, true));
        if ($technicalSkillMatch) $labels[] = 'Technical Skills';
        $score += $technicalSkillMatch * $weights['technical_skills'];

        // m1b Other Skills (only if no technical match)
        if (!$technicalSkillMatch) {
            $otherSkillMatch = (int)collect($normalizedGraduateOtherSkills)
                ->contains(fn($os) => in_array($os, $normalizedJobSkills, true));
            if ($otherSkillMatch) {
                $labels[] = 'Other Skills (Soft, Language)';
                $score += $otherSkillMatch * $weights['other_skills'];
            }
        }

        // m2 Education
        $educationMatch = ($graduateProgram && in_array($graduateProgram, $jobProgramNames, true)) ? 1 : 0;
        if ($educationMatch) $labels[] = 'Education';
        $score += $educationMatch * $weights['education'];

        // m3 Experience (job title contains any graduate experience title)
        $experienceMatch = 0;
        $jobTitleLower = mb_strtolower($job->job_title ?? '');
        foreach ($experienceTitles as $title) {
            if ($title && strpos($jobTitleLower, mb_strtolower($title)) !== false) {
                $experienceMatch = 1;
                $labels[] = 'Experience';
                break;
            }
        }
        $score += $experienceMatch * $weights['experience'];

        // m4 Preferred Job Type (overlap)
        $jobTypeMatch = count(array_intersect($preferredJobTypes, $jobTypeNames)) > 0 ? 1 : 0;
        if ($jobTypeMatch) $labels[] = 'Preferred Job Type';
        $score += $jobTypeMatch * $weights['job_type'];

        // m5 Preferred Location (case-insensitive)
        $locationMatch = 0;
        if ($normPreferredLocations && $normJobLocations) {
            $locationMatch = count(array_intersect($normPreferredLocations, $normJobLocations)) > 0 ? 1 : 0;
        }
        if ($locationMatch) $labels[] = 'Preferred Location';
        $score += $locationMatch * $weights['location'];

        // m6 Work Environment (any overlap)
        $workEnvMatch = count(array_intersect(
            array_map('mb_strtolower', $preferredWorkEnvironments),
            array_map('mb_strtolower', $jobWorkEnvironment)
        )) > 0 ? 1 : 0;
        if ($workEnvMatch) $labels[] = 'Preferred Work Environment';
        $score += $workEnvMatch * $weights['work_environment'];

        // m7 Min Salary (job must meet or exceed preferred min)
        $minSalaryMatch = ($minSalaryPref && $jobMinSalary !== null && $jobMinSalary >= $minSalaryPref) ? 1 : 0;
        if ($minSalaryMatch) $labels[] = 'Preferred Min Salary';
        $score += $minSalaryMatch * $weights['min_salary'];

        // m8 Max Salary (job max within preferred max if both defined)
        $maxSalaryMatch = ($maxSalaryPref && $jobMaxSalary !== null && $jobMaxSalary <= $maxSalaryPref) ? 1 : 0;
        if ($maxSalaryMatch) $labels[] = 'Preferred Max Salary';
        $score += $maxSalaryMatch * $weights['max_salary'];

        // m9 Salary Type
        $salaryTypeMatch = ($salaryTypePref && $jobSalaryType && stripos($jobSalaryType, $salaryTypePref) !== false) ? 1 : 0;
        if ($salaryTypeMatch) $labels[] = 'Preferred Salary Type';
        $score += $salaryTypeMatch * $weights['salary_type'];

        // m10 Keywords (optional; look for provided searchKeywords inside title/description)
        $keywordsMatch = 0;
        if ($searchKeywords) {
            $kw        = mb_strtolower($searchKeywords);
            $titleLow  = mb_strtolower($job->job_title ?? '');
            $descLow   = mb_strtolower($job->job_description ?? '');
            if (strpos($titleLow, $kw) !== false || strpos($descLow, $kw) !== false) {
                $keywordsMatch = 1;
                $labels[] = 'Keywords';
            }
        }
        $score += $keywordsMatch * $weights['keywords'];

        $match_percentage = $totalWeight > 0 ? round(($score / $totalWeight) * 100) : 0;

        // Threshold consistent with recommendations() logic
        $threshold = 60;
        if ($match_percentage >= $threshold) {
            $screening_label    = 'Shortlisted';
            $screening_feedback = 'Auto-screened: High match (' . $match_percentage . '%)';
            $is_shortlisted     = true;
            $status             = 'screening';
        } else {
            $screening_label    = 'Review Further';
            $screening_feedback = 'Auto-screened: Match (' . $match_percentage . '%)';
            $is_shortlisted     = false;
            $status             = 'applied';
        }

        return [
            'labels'             => array_values(array_unique($labels)),
            'score'              => $score,
            'match_percentage'   => $match_percentage,
            'screening_label'    => $screening_label,
            'screening_feedback' => $screening_feedback,
            'is_shortlisted'     => $is_shortlisted,
            'status'             => $status,
        ];
    }
}