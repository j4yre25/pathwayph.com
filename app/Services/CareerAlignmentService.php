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

        $jobTitle = strtolower((string) ($graduate->current_job_title ?? ''));

        // 1. Degree Match
        $degreeMatch = InstitutionProgram::where('institution_id', $graduate->institution_id)
            ->where('program_id', $graduate->program_id)
            ->exists() ? 1 : 0;

        if (!$degreeMatch) {
            // No degree match, always 0%
            $graduate->alignment_score = 0;
            $graduate->save();
            return [
                'score' => 0,
                'percentage' => 0,
                'alignment' => 'Misaligned',
                'degreeMatch' => 0,
                'skillsMatch' => 0,
                'careerOpportunityMatch' => 0,
            ];
        }

        // 2. Career Opportunity Match (for this degree/program)
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

        // 3. Skills Match
        $graduateSkills = $graduate->graduateSkills
            ->pluck('skill.name')
            ->map(fn($s) => strtolower((string) $s))
            ->toArray();

        $matchedCareerOpportunityId = null;
        foreach ($careerOpportunities as $opportunity) {
            if (stripos($jobTitle, $opportunity) !== false) {
                $matchedCareerOpportunityId = InstitutionCareerOpportunity::where('institution_id', $graduate->institution_id)
                    ->where('program_id', $graduate->program_id)
                    ->whereHas('careerOpportunity', function($q) use ($opportunity) {
                        $q->whereRaw('LOWER(title) = ?', [$opportunity]);
                    })
                    ->value('career_opportunity_id');
                break;
            }
        }

        if ($matchedCareerOpportunityId) {
            // Only use skills for the matched career opportunity
            $institutionSkills = InstitutionSkill::where('institution_id', $graduate->institution_id)
                ->where('program_id', $graduate->program_id)
                ->where('career_opportunity_id', $matchedCareerOpportunityId)
                ->where('skill_id', '!=', null)
                ->get()
                ->pluck('skill.name')
                ->map(fn($s) => strtolower($s))
                ->unique()
                ->toArray();
        } else {
            // Use all skills for the degree/program
            $institutionSkills = InstitutionSkill::where('institution_id', $graduate->institution_id)
                ->where('program_id', $graduate->program_id)
                ->where('skill_id', '!=', null)
                ->get()
                ->pluck('skill.name')
                ->map(fn($s) => strtolower($s))
                ->unique()
                ->toArray();
        }

        $matchingSkills = array_intersect($graduateSkills, $institutionSkills);
        $skillsMatch = count($institutionSkills) > 0 ? count($matchingSkills) / count($institutionSkills) : 0;

        // 4. Scoring
        $percentage = 0;
        if ($degreeMatch) {
            if ($careerOpportunityMatch && $skillsMatch == 1) {
                $percentage = 100;
            } elseif ($careerOpportunityMatch && $skillsMatch < 1 && $skillsMatch > 0) {
                $percentage = 70 + round($skillsMatch * 30); // 70-100%
            } elseif ($careerOpportunityMatch && $skillsMatch == 0) {
                $percentage = 70;
            } elseif (!$careerOpportunityMatch && $skillsMatch > 0) {
                $percentage = 50 + round($skillsMatch * 30); // 50-90% depending on skills
            } elseif (!$careerOpportunityMatch && $skillsMatch == 0) {
                $percentage = 50;
            }
        } else {
            $percentage = 0;
        }

        // Alignment Category
        if ($percentage >= 80) {
            $alignment = 'Directly aligned';
        } elseif ($percentage >= 60) {
            $alignment = 'Partially aligned';
        } elseif ($percentage > 0) {
            $alignment = 'Misaligned';
        } else {
            $alignment = 'Misaligned';
        }

        // --- Real-time update in graduates table ---
        $graduate->alignment_score = $percentage;
        $graduate->save();

        return [
            'score' => $percentage / 100,
            'percentage' => $percentage,
            'alignment' => $alignment,
            'degreeMatch' => $degreeMatch,
            'skillsMatch' => $skillsMatch,
            'careerOpportunityMatch' => $careerOpportunityMatch,
        ];
    }
}
