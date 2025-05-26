<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Portfolio;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Skill;
use App\Models\Project;
use App\Models\Certification;
use Inertia\Inertia;

class CompanyApplicationController extends Controller
{
    /**
     * Display the specified graduate's portfolio.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewPortfolio($userId)
    {
        // Get the graduate user
        $user = User::findOrFail($userId);
        
        // Get the graduate's portfolio data
        $portfolio = Portfolio::where('user_id', $userId)->first();
        $education = Education::where('user_id', $userId)->get();
        $experience = Experience::where('user_id', $userId)->get();
        $skills = Skill::where('user_id', $userId)->get();
        $projects = Project::where('user_id', $userId)->get();
        $certifications = Certification::where('user_id', $userId)->get();
        
        // Format the data for the GraduatePortfolio component
        $graduateData = [
            'name' => $user->graduate_first_name . ' ' . $user->graduate_middle_initial . ' ' . $user->graduate_last_name,
            'title' => $user->graduate_professional_title ?? '',
            'location' => $user->graduate_location ?? '',
            'phone' => $user->contact_number ?? '',
            'email' => $user->email,
            'degree' => $user->graduate_program_completed ?? '',
            'socialLinks' => [
                // Add social links if available
                ['name' => 'LinkedIn', 'icon' => 'fab fa-linkedin', 'url' => $user->linkedin_url ?? ''],
                ['name' => 'GitHub', 'icon' => 'fab fa-github', 'url' => $user->github_url ?? '']
            ],
            'about' => $user->graduate_about_me ?? '',
            'personalInfo' => [
                'location' => $user->graduate_location ?? '',
                'birthdate' => $user->dob ?? '',
                'gender' => $user->gender ?? '',
                'ethnicity' => $user->graduate_ethnicity ?? '',
                'graduated' => $user->graduate_year_graduated ?? '',
                'school' => $user->graduate_school_graduated_from ?? ''
            ],
            'education' => $education->map(function($item) {
                return [
                    'institution' => $item->graduate_education_institution_id ?? '',
                    'degree' => $item->graduate_education_degree ?? '',
                    'program' => $item->graduate_education_program ?? '',
                    'startDate' => $item->graduate_education_start_date ?? '',
                    'endDate' => $item->graduate_education_end_date ?? '',
                    'description' => $item->graduate_education_description ?? ''
                ];
            }),
            'skills' => $skills->map(function($item) {
                return [
                    'name' => $item->graduate_skill_name ?? '',
                    'category' => $item->graduate_skill_category ?? 'General',
                    'proficiency' => $item->graduate_skill_proficiency ?? 'Intermediate'
                ];
            }),
            'skillCategories' => [
                ['name' => 'Technical', 'icon' => 'fas fa-code'],
                ['name' => 'Soft Skills', 'icon' => 'fas fa-comments'],
                ['name' => 'Languages', 'icon' => 'fas fa-language'],
                ['name' => 'Tools', 'icon' => 'fas fa-tools']
            ],
            'workExperience' => $experience->map(function($item) {
                return [
                    'company' => $item->graduate_experience_company ?? '',
                    'title' => $item->graduate_experience_title ?? '',
                    'startDate' => $item->graduate_experience_start_date ?? '',
                    'endDate' => $item->graduate_experience_end_date ?? '',
                    'description' => $item->graduate_experience_description ?? '',
                    'location' => $item->graduate_experience_location ?? ''
                ];
            }),
            'projects' => $projects->map(function($item) {
                return [
                    'name' => $item->graduate_projects_name ?? '',
                    'role' => $item->graduate_projects_role ?? '',
                    'description' => $item->graduate_projects_description ?? '',
                    'technologies' => $item->graduate_projects_technologies ?? '',
                    'url' => $item->graduate_projects_url ?? ''
                ];
            }),
            'certifications' => $certifications->map(function($item) {
                return [
                    'name' => $item->graduate_certification_name ?? '',
                    'issuer' => $item->graduate_certification_issuer ?? '',
                    'date' => $item->graduate_certification_date ?? '',
                    'description' => $item->graduate_certification_description ?? ''
                ];
            })
        ];
        
        return response()->json($graduateData);
    }
}
