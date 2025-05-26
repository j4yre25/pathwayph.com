<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Graduate;
use App\Models\GraduateSkill;
use App\Models\Project;
use App\Models\Resume;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class GraduateProfileController extends Controller
{


    public function show($id)
    {
        $graduate = Graduate::with('user')->findOrFail($id);

        return inertia('Frontend/GraduateProfile', [
            'graduate' => $graduate,
            'skills' => \App\Models\GraduateSkill::where('graduate_id', $graduate->id)
                ->join('skills', 'graduate_skills.skill_id', '=', 'skills.id')
                ->select('graduate_skills.*', 'skills.name as skill_name')
                ->get(),
            'experiences' => Experience::where('graduate_id', $graduate->id)->get(),
            'education' => Education::where('graduate_id', $graduate->id)->get(),
            'projects' => Project::where('graduate_id', $graduate->id)->get(),
            'achievements' => Achievement::where('graduate_id', $graduate->id)->get(),
            'certifications' => Certification::where('graduate_id', $graduate->id)->get(),
            'testimonials' => Testimonial::where('graduate_id', $graduate->id)->get(),
            'employmentPreferences' => \App\Models\EmploymentPreference::where('graduate_id', $graduate->id)->first(), // <-- Add this line
            'careerGoals' => \App\Models\CareerGoal::where('graduate_id', $graduate->id)->first(), // <-- Add this line
            'resume' => Resume::where('graduate_id', $graduate->id)->first(),
        ]);
    }
}
