<?php

namespace App\Services;

use App\Models\Graduate;
use App\Models\InstitutionProgram;
use App\Models\InstitutionSkill;
use App\Models\InstitutionCareerOpportunity;

class CareerAlignmentService
{
    public static function score(Graduate $graduate)
    {
        // If unemployed, show UNEMPLOYED and skip calculation
        if (
            strtolower($graduate->employment_status) === 'unemployed' ||
            strtolower($graduate->current_job_title) === 'n/a'
        ) {
            $graduate->alignment_score = null;
            $graduate->save();

            return [
                'score' => null,
                'percentage' => null,
                'alignment' => 'UNEMPLOYED',
                'degreeMatch' => null,
                'skillsMatch' => null,
                'careerOpportunityMatch' => null,
            ];
        }

        // Degree Match: Check if graduate's job title matches any career opportunity for their degree program
        $degreeMatch = 0;
        $jobTitle = strtolower((string) ($graduate->current_job_title ?? ''));
        $degreeCareerOpportunities = InstitutionCareerOpportunity::where('institution_id', $graduate->institution_id)
            ->where('program_id', $graduate->program_id)
            ->with('careerOpportunity')
            ->get()
            ->pluck('careerOpportunity.title')
            ->map(fn($t) => strtolower($t))
            ->unique()
            ->toArray();

        // Use stripos for case-insensitive matching
        foreach ($degreeCareerOpportunities as $opportunity) {
            if (stripos($jobTitle, $opportunity) !== false) {
                $degreeMatch = 1;
                break;
            }
        }

        // Skills Match
        $graduateSkills = $graduate->graduateSkills
            ->pluck('skill.name')
            ->map(fn($s) => strtolower((string) $s))
            ->toArray();
        $institutionSkills = InstitutionSkill::where('institution_id', $graduate->institution_id)
            ->where('career_opportunity_id', $graduate->career_opportunity_id ?? null)
            ->where('skill_id', '!=', null)
            ->get()
            ->pluck('skill.name')
            ->map(fn($s) => strtolower($s))
            ->unique()
            ->toArray();

        $matchingSkills = array_intersect($graduateSkills, $institutionSkills);
        $skillsMatch = count($institutionSkills) > 0 ? count($matchingSkills) / count($institutionSkills) : 0;

        // Career Opportunity Match
        $careerOpportunities = InstitutionCareerOpportunity::where('institution_id', $graduate->institution_id)
            ->where('program_id', $graduate->program_id)
            ->with('careerOpportunity')
            ->get()
            ->pluck('careerOpportunity.title')
            ->map(fn($t) => strtolower($t))
            ->unique()
            ->toArray();

        $careerOpportunityMatch = 0;
        foreach ($careerOpportunities as $opportunity) {
            if (stripos($jobTitle, $opportunity) !== false) {
                $careerOpportunityMatch = 1;
                break;
            }
        }

        // Final Score
        $score = (0.5 * $degreeMatch) + (0.3 * $skillsMatch) + (0.2 * $careerOpportunityMatch);
        $percentage = round($score * 100);

        // Alignment Category
        if ($percentage >= 80) {
            $alignment = 'Directly aligned';
        } elseif ($percentage >= 60) {
            $alignment = 'Partially aligned';
        } else {
            $alignment = 'Misaligned';
        }

        // --- Real-time update in graduates table ---
        $graduate->alignment_score = $percentage;
        $graduate->save();

        return [
            'score' => $score,
            'percentage' => $percentage,
            'alignment' => $alignment,
            'degreeMatch' => $degreeMatch,
            'skillsMatch' => $skillsMatch,
            'careerOpportunityMatch' => $careerOpportunityMatch,
        ];
    }
}
