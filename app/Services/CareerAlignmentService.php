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
        // --- 1. Skip if unemployed ---
        if (
            strtolower($graduate->employment_status) === 'unemployed' ||
            strtolower($graduate->current_job_title) === 'n/a'
        ) {
            $graduate->alignment_score = null;
            $graduate->normalized_alignment_score = null;
            $graduate->save();

            return [
                'score' => null,
                'percentage' => null,
                'normalized' => null,
                'alignment' => 'UNEMPLOYED',
            ];
        }

        $jobTitle = strtolower((string) ($graduate->current_job_title ?? ''));

        // --- 2. Degree Match ---
        $degreeMatch = InstitutionProgram::where('institution_id', $graduate->institution_id)
            ->where('program_id', $graduate->program_id)
            ->exists() ? 1 : 0;

        if (!$degreeMatch) {
            $graduate->alignment_score = 0;
            $graduate->normalized_alignment_score = 0;
            $graduate->save();

            return [
                'score' => 0,
                'percentage' => 0,
                'normalized' => 0,
                'alignment' => 'Misaligned',
            ];
        }

        // --- 3. Career Opportunity Match ---
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

        // --- 4. Skills Match ---
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
            $institutionSkills = InstitutionSkill::where('institution_id', $graduate->institution_id)
                ->where('program_id', $graduate->program_id)
                ->where('career_opportunity_id', $matchedCareerOpportunityId)
                ->whereNotNull('skill_id')
                ->get()
                ->pluck('skill.name')
                ->map(fn($s) => strtolower($s))
                ->unique()
                ->toArray();
        } else {
            $institutionSkills = InstitutionSkill::where('institution_id', $graduate->institution_id)
                ->where('program_id', $graduate->program_id)
                ->whereNotNull('skill_id')
                ->get()
                ->pluck('skill.name')
                ->map(fn($s) => strtolower($s))
                ->unique()
                ->toArray();
        }

        $matchingSkills = array_intersect($graduateSkills, $institutionSkills);
        $skillsMatch = count($institutionSkills) > 0 ? count($matchingSkills) / count($institutionSkills) : 0;

        // --- 5. Raw Scoring Formula (0–100%) ---
        $percentage = 0;
        if ($degreeMatch) {
            if ($careerOpportunityMatch && $skillsMatch == 1) {
                $percentage = 100;
            } elseif ($careerOpportunityMatch && $skillsMatch > 0) {
                $percentage = 70 + round($skillsMatch * 30); // 70–100%
            } elseif ($careerOpportunityMatch && $skillsMatch == 0) {
                $percentage = 70;
            } elseif (!$careerOpportunityMatch && $skillsMatch > 0) {
                $percentage = 50 + round($skillsMatch * 30); // 50–90%
            } else {
                $percentage = 50;
            }
        }

        // --- 6. Alignment Category ---
        if ($percentage >= 80) {
            $alignment = 'Directly aligned';
        } elseif ($percentage >= 60) {
            $alignment = 'Partially aligned';
        } else {
            $alignment = 'Misaligned';
        }

        // --- 7. Save Raw Alignment ---
        $graduate->alignment_score = $percentage;
        $graduate->save();

        // --- 8. Normalization (Based on all graduates’ raw scores) ---
        $min = Graduate::whereNotNull('alignment_score')->min('alignment_score');
        $max = Graduate::whereNotNull('alignment_score')->max('alignment_score');

        if ($max == $min) {
            $normalized = 0;
        } else {
            $normalized = ($percentage - $min) / ($max - $min);
        }

        // --- 9. Save Normalized Score ---
        $graduate->normalized_alignment_score = round($normalized, 3);
        $graduate->save();

        // --- 10. Return Results ---
        return [
            'score' => round($percentage / 100, 3),
            'percentage' => $percentage,
            'normalized' => round($normalized, 3),
            'alignment' => $alignment,
            'degreeMatch' => $degreeMatch,
            'skillsMatch' => $skillsMatch,
            'careerOpportunityMatch' => $careerOpportunityMatch,
        ];
    }
}
