<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Portfolio;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Graduate;
use App\Models\Skill;
use App\Models\Project;
use App\Models\Certification;
use App\Models\Achievement;
use App\Models\CareerGoal;
use App\Models\Testimonial;
use App\Models\EmploymentPreference;

class GraduatePortfolioController extends Controller
{
    // Display graduate portfolio page
    public function index()
    {
        $user = Auth::user();
        $graduate = Graduate::with(['program.degree', 'schoolYear'])
            ->where('user_id', $user->id)
            ->first();

        // Get all the graduate's profile data
        $portfolio = Portfolio::where('user_id', $user->id)->first();
        
        return Inertia::render('Frontend/GraduatePortfolio', [
            'graduateData' => [
                'name' => $user->name,
                'title' => $graduate ? $graduate->program->name : '',
                'location' => $user->location ?? '',
                'phone' => $user->phone ?? '',
                'email' => $user->email,
                'degree' => $graduate ? $graduate->program->degree->name : '',
                'socialLinks' => $user->social_links ?? [],
                'about' => $portfolio ? $portfolio->graduate_about_me : '',
                'careerGoals' => (function() use ($graduate) {
                    if (!$graduate) {
                        return [
                            'shortTerm' => '',
                            'longTerm' => '',
                            'industries' => '',
                            'careerPath' => ''
                        ];
                    }
                    
                    $careerGoal = CareerGoal::where('graduate_id', $graduate->id)->first();
                    
                    if (!$careerGoal) {
                        return [
                            'shortTerm' => '',
                            'longTerm' => '',
                            'industries' => '',
                            'careerPath' => ''
                        ];
                    }
                    
                    return [
                        'shortTerm' => $careerGoal->short_term_goals ?? '',
                        'longTerm' => $careerGoal->long_term_goals ?? '',
                        'industries' => $careerGoal->industries_of_interest ?? '',
                        'careerPath' => $careerGoal->career_path ?? ''
                    ];
                })(),
                'employmentPreferences' => EmploymentPreference::where('user_id', $user->id)->get() ?? [],
                'personalInfo' => [
                    'location' => $user->location ?? '',
                    'birthdate' => $user->birthdate ?? '',
                    'gender' => $user->gender ?? '',
                    'ethnicity' => $user->ethnicity ?? '',
                    'graduated' => $graduate ? $graduate->schoolYear->name : '',
                    'school' => $graduate ? $graduate->program->degree->institution->name : ''
                ],
                'education' => Education::where('user_id', $user->id)->get(),
                'educationSummary' => $portfolio ? $portfolio->graduate_education_summary : '',
                'academicAchievements' => Achievement::where('user_id', $user->id)
                    ->where('type', 'academic')
                    ->get(),
                'skillCategories' => Skill::where('user_id', $user->id)
                    ->select('category')
                    ->distinct()
                    ->get()
                    ->map(function ($skill) {
                        return ['name' => $skill->category];
                    }),
                'skills' => Skill::where('user_id', $user->id)->get(),
                'workExperience' => Experience::where('user_id', $user->id)->get(),
                'careerHighlights' => Experience::where('user_id', $user->id)
                    ->where('is_highlight', true)
                    ->get(),
                'projects' => Project::where('user_id', $user->id)->get(),
                'certifications' => Certification::where('user_id', $user->id)->get(),
                'achievements' => Achievement::where('user_id', $user->id)
                    ->where('type', 'professional')
                    ->get(),
                'testimonials' => Testimonial::where('user_id', $user->id)->get(),
                'contactInfo' => [
                    'location' => $user->location ?? '',
                    'email' => $user->email,
                    'phone' => $user->phone ?? '',
                    'website' => $user->website ?? '',
                    'socialProfiles' => $user->social_links ?? []
                ],
                'contactForm' => [
                    'name' => '',
                    'email' => '',
                    'subject' => '',
                    'message' => ''
                ],
            ]
        ]);
    }
    
    /**
     * Handle contact form submission
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitContactForm(Request $request): JsonResponse
    {
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|min:2',
            'email' => 'required|email',
            'subject' => 'required|min:5',
            'message' => 'required|min:10',
        ]);
        
        // Get the authenticated user
        $user = Auth::user();
        
        // Here you would typically save the contact form data to the database
        // For example, you could create a ContactMessage model and save the data:
        // \App\Models\ContactMessage::create([
        //     'user_id' => $user->id,
        //     'name' => $validated['name'],
        //     'email' => $validated['email'],
        //     'subject' => $validated['subject'],
        //     'message' => $validated['message'],
        //     'status' => 'unread'
        // ]);
        
        // Or send an email using Laravel's Mail facade:
        // \Illuminate\Support\Facades\Mail::to('admin@example.com')
        //     ->send(new \App\Mail\ContactFormSubmission($validated));
        
        // Log the contact form submission
        Log::info('Contact form submitted by: ' . $user->name, [
            'user_id' => $user->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject']
        ]);
        
        // Return a JSON response for the API
        return response()->json([
            'success' => true,
            'message' => 'Your message has been sent successfully!'
        ]);
    }
}