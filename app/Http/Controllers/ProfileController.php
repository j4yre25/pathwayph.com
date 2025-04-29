<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\EducationUpdateRequest;
use App\Http\Requests\SkillUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\Education;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Experience;
use App\Models\Certification;
use App\Models\Achievement;
use App\Models\EmploymentPreference;
use App\Models\Testimonial;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;



class ProfileController extends Controller
{
    // Show the profile settings page
    public function index()
    {
        $user = Auth::user();
     
     
        return Inertia::render('Frontend/Profile', [
            'user' => Auth::user(),
            'educationEntries' => Education::where('user_id', $user->id)->get(),
            'experienceEntries' => Experience::where('user_id', $user->id)->get(), 
            'skillEntries' => Skill::where('user_id', $user-> id)->get(),
            'certificationsEntries' => Certification::where('user_id', $user->id)->get(),
            'projectsEntries' => Project::where('user_id', $user->id)->get(),
            'achievementEntries' => Achievement::where('user_id', $user->id)->get(),
            'testimonialsEntries' => Testimonial::where('user_id', $user->id)->get(), 
            'employmentPreferences' => EmploymentPreference::where('user_id', $user->id)->first(),
            'careerGoals' => Auth::user()->careerGoals,
            'resume' => Auth::user()->resume,
        ]);
    }

    // Update profile information
    public function updateProfile(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'graduate_first_name' => 'required|string|max:255',
            'graduate_middle_initial' => 'nullable|string|max:1',
            'graduate_last_name' => 'required|string|max:255',
            'graduate_professional_title' => 'required|string|max:255',
            'graduate_location' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'contact_number' => 'nullable|string|max:15',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
            'graduate_ethnicity' => 'nullable|string|max:255',
            'graduate_address' => 'nullable|string|max:255',
            'graduate_about_me' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $user->update($validated);
        //dd('Updated User Data:', $user->toArray());

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    // Add education
    public function addEducation(Request $request)
    {
        $request->validate([
            'graduate_education_institution_id' => 'required|string|max:255',
            'graduate_education_program' => 'required|string|max:255',
            'graduate_education_field_of_study' => 'required|string|max:255',
            'graduate_education_start_date' => 'required|date',
            'graduate_education_end_date' => 'nullable|date', // Allow null
            'graduate_education_description' => 'nullable|string',
            'is_current' => 'boolean',
            'achievements' => 'nullable|string',
            'no_achievements' => 'nullable|string',
        ]);

        $data = $request->all();
        if ($request->is_current) {
            $data['graduate_education_end_date'] = null; // Use null for "present"
        }

        Log::info('Data being saved:', $data); // Debugging: Log the data

        $education = new Education($data);
        $education->user_id = Auth::id();

        try {
            $education->save();
            return Redirect()->back()->with('flash.banner', 'Education added successfully.');
        } catch (\Exception $e) {
            Log::error('Error saving education:', ['error' => $e->getMessage()]); // Debugging: Log the error
            return Redirect()->back()->with('flash.banner', 'An error occurred while adding education. Please try again.');
        }
    }

    // Update education
    public function updateEducation(Request $request, $id)
    {
        $request->validate([
            'graduate_education_institution_id' => 'required|string|max:255',
            'graduate_education_program' => 'required|string|max:255',
            'graduate_education_field_of_study' => 'required|string|max:255',
            'graduate_education_start_date' => 'required|date',
            'graduate_education_end_date' => 'nullable|date',
            'graduate_education_description' => 'nullable|string',
            'is_current' => 'boolean',
            'achievements' => 'nullable|string',
            'no_achievements' => 'nullable|boolean', // Add this validation rule
        ]);

        $data = $request->all();

        // Handle is_current logic
        if ($request->is_current) {
            $data['graduate_education_end_date'] = 'present';
        }

        $education = Education::findOrFail($id);
        $education->update($data);

        return Redirect()->back()->with('flash.banner', 'Education updated successfully.');
    }

    // Remove education
    public function removeEducation($id)
    {
        $education = Education::findOrFail($id);
        $education->delete();

        return Redirect()->back()->with('flash.banner', 'Education removed successfully.');
    }

    // Add experience
    public function addExperience(Request $request)
    {
        $request->validate([
            'graduate_experience_title' => 'required|string|max:255',
            'graduate_experience_company' => 'required|string|max:255',
            'graduate_experience_start_date' => 'required|date',
            'graduate_experience_end_date' => 'nullable|date',
            'graduate_experience_address' => 'nullable|string|max:255',
            'graduate_experience_description' => 'nullable|string',
            'graduate_experience_employment_type' => 'nullable|string|max:255', // Keep this line
            'is_current' => 'boolean',
        ]);

        $data = $request->all();
        

        if (isset($data['graduate_experience_start_date'])) {
            $data['graduate_experience_start_date'] = Carbon::parse($data['graduate_experience_start_date'])->format('Y-m-d H:i:s');
        }
    
        if (isset($data['graduate_experience_end_date'])) {
            $data['graduate_experience_end_date'] = Carbon::parse($data['graduate_experience_end_date'])->format('Y-m-d H:i:s');
        }

        if ($request->is_current) {
            $data['graduate_experience_end_date'] = null; 
        }

        $data['graduate_experience_description'] = $data['graduate_experience_description'] ?? 'No description provided';

        $experience = new Experience($data);
        $experience->user_id = Auth::id();

            $experience->save();
            return redirect()->back()->with('flash.banner', 'Experience added successfully.');
     
    }

    // Update experience
    public function updateExperience(Request $request, $id)
    {
        $request->validate([
            'graduate_experience_title' => 'required|string|max:255',
            'graduate_experience_company' => 'required|string|max:255',
            'graduate_experience_start_date' => 'required|date',
            'graduate_experience_end_date' => 'nullable|date',
            'graduate_experience_address' => 'nullable|string|max:255',
            'graduate_experience_description' => 'nullable|string',
            'graduate_experience_employment_type' => 'nullable|string|max:255', 
            'is_current' => 'boolean',
        ]);

        $data = $request->all();

        if (isset($data['graduate_experience_start_date'])) {
            $data['graduate_experience_start_date'] = Carbon::parse($data['graduate_experience_start_date'])->format('Y-m-d H:i:s');
        }
    
        if (isset($data['graduate_experience_end_date'])) {
            $data['graduate_experience_end_date'] = Carbon::parse($data['graduate_experience_end_date'])->format('Y-m-d H:i:s');
        }
        
        if ($request->is_current) {
            $data['graduate_experience_end_date'] = null; 
        }
        
        if ($request->is_current) {
            $data['graduate_experience_end_date'] = null; 
        }

        $experience = Experience::findOrFail($id);
        $experience->update($data);

        return redirect()->back()->with('flash.banner', 'Experience updated successfully.');
    }

    // Remove experience
    public function removeExperience($id)
    {
        $experience = Experience::findOrFail($id);
        $experience->delete();

        return Redirect()->back()->with('flash.banner', 'Experience removed successfully.');
    }

    // Add skill
    public function addSkill(Request $request)
    {
        Log::info('Request data:', $request->all()); // Log the incoming request data
    
        $request->validate([
            'graduate_skills_name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = Skill::where('user_id', Auth::id())
                        ->where('graduate_skills_name', $value)
                        ->exists();
                    if ($exists) {
                        $fail('The skill "' . $value . '" already exists.');
                    }
                },
            ],
            'graduate_skills_proficiency_type' => 'required|string|in:Beginner,Intermediate,Advanced,Expert', // Restrict to specific values
            'graduate_skills_type' => 'required|string|max:255',
            'graduate_skills_years_experience' => 'required|integer|min:0',
        ]);
    
        // Create a new skill instance
        $skill = new Skill();
        $skill->graduate_skills_name = $request->graduate_skills_name;
        $skill->graduate_skills_proficiency_type = $request->graduate_skills_proficiency_type; // Ensure this is set correctly
        $skill->graduate_skills_type = $request->graduate_skills_type;
        $skill->graduate_skills_years_experience = $request->graduate_skills_years_experience;
        $skill->user_id = Auth::id(); // Associate with the authenticated user
        $skill->save();
    
        return Redirect()->back()->with('flash.banner', 'Skill added successfully.');
    }
    // Update skilL
    public function updateSkill(Request $request, $id)
    {
        Log::info('Update request data:', $request->all());

        $request->validate([
            'graduate_skills_name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($id) {
                    $exists = Skill::where('user_id', Auth::id())
                        ->where('graduate_skills_name', $value)
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($exists) {
                        $fail('The skill "' . $value . '" already exists.');
                    }
                },
            ],
            'graduate_skills_proficiency_type' => 'required|string|in:Beginner,Intermediate,Advanced,Expert',
            'graduate_skills_type' => 'required|string|max:255',
            'graduate_skills_years_experience' => 'required|integer|min:0',
        ]);

        $skill = Skill::findOrFail($id); 

        $skill->graduate_skills_name = $request->graduate_skills_name;
        $skill->graduate_skills_proficiency_type = $request->graduate_skills_proficiency_type;
        $skill->graduate_skills_type = $request->graduate_skills_type;
        $skill->graduate_skills_years_experience = $request->graduate_skills_years_experience;
        $skill->user_id = Auth::id();

        $skill->save();

        return redirect()->back()->with('flash.banner', 'Skill updated successfully.');
    }

        // Remove skill
        public function removeSkill($id)
        {
            
            $skill = Skill::findOrFail($id);
            $skill->delete();

            return Redirect()->back()->with('flash.banner', 'Skill added successfully.');
        }

    // Add Project
    public function addProject(Request $request)
    {
        try {
            $user = Auth::user();
            
            $project = new Project();
            $project->user_id = $user->id;
            $project->graduate_projects_title = $request->graduate_projects_title;
            $project->graduate_projects_description = $request->graduate_projects_description;
            $project->graduate_projects_role = $request->graduate_projects_role;
            $project->graduate_projects_start_date = $request->graduate_projects_start_date;
            $project->graduate_projects_end_date = $request->is_current ? null : $request->graduate_projects_end_date;
            $project->graduate_projects_url = $request->graduate_projects_url;
            $project->graduate_projects_key_accomplishments = $request->graduate_projects_key_accomplishments;
            $project->is_current = $request->is_current;
            
            $project->save();
            
            return redirect()->back()->with('flash.banner', 'Project added successfully.');
        } catch (\Exception $e) {
            Log::error('Error adding project: ' . $e->getMessage());
            return redirect()->back()->with('flash.banner', 'Failed to add project. Please try again.');
        }
    }
    
    public function updateProject(Request $request, $id)
    {
        try {
            $project = Project::findOrFail($id);
            
            if ($project->user_id !== Auth::id()) {
                return redirect()->back()->with('flash.banner', 'Unauthorized access.');
            }
            
            $project->graduate_projects_title = $request->graduate_projects_title;
            $project->graduate_projects_description = $request->graduate_projects_description;
            $project->graduate_projects_role = $request->graduate_projects_role;
            $project->graduate_projects_start_date = $request->graduate_projects_start_date;
            $project->graduate_projects_end_date = $request->is_current ? null : $request->graduate_projects_end_date;
            $project->graduate_projects_url = $request->graduate_projects_url;
            $project->graduate_projects_key_accomplishments = $request->graduate_projects_key_accomplishments;
            $project->is_current = $request->is_current;
            
            $project->save();
            
            return redirect()->back()->with('flash.banner', 'Project updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating project: ' . $e->getMessage());
            return redirect()->back()->with('flash.banner', 'Failed to update project. Please try again.');
        }
    }
    
    public function removeProject($id)
    {
        try {
            $project = Project::findOrFail($id);
            
            if ($project->user_id !== Auth::id()) {
                return redirect()->back()->with('flash.banner', 'Unauthorized access.');
            }
            
            $project->delete();
            
            return redirect()->back()->with('flash.banner', 'Project removed successfully.');
        } catch (\Exception $e) {
            Log::error('Error removing project: ' . $e->getMessage());
            return redirect()->back()->with('flash.banner', 'Failed to remove project. Please try again.');
        }
    }

    // Add certification
    public function addCertification(Request $request)
    {
        $request->validate([
            'graduate_certification_name' => 'required|string|max:255',
            'graduate_certification_issuer' => 'required|string|max:255',
            'graduate_certification_issue_date' => 'required|date',
            'graduate_certification_expiry_date' => 'nullable|date',
            'graduate_certification_credential_id' => 'nullable|string|max:255',
            'graduate_certification_credential_url' => 'nullable|string|max:255',
        ]);

        $certification = new Certification($request->all());

        if (isset($certification['graduate_certification_issue_date'])) {
            $certification['graduate_certification_issue_date'] = Carbon::parse($certification['graduate_certification_issue_date'])->format('Y-m-d H:i:s');
        }
    
        if (isset($certification['graduate_certification_expiry_date'])) {
            $certification['graduate_certification_expiry_date'] = Carbon::parse($certification['graduate_certification_expiry_date'])->format('Y-m-d H:i:s');
        }

        $certification->user_id = Auth::id(); // Assuming you have a user_id field
        $certification->save();

        return Redirect()->back()->with('flash.banner', 'Certification added successfully.');
    }

    // Update certification
    public function updateCertification(Request $request, $id)
    {
        $request->validate([
            'graduate_certification_name' => 'required|string|max:255',
            'graduate_certification_issuer' => 'required|string|max:255',
            'graduate_certification_issue_date' => 'required|date',
            'graduate_certification_expiry_date' => 'nullable|date',
            'graduate_certification_credential_id' => 'nullable|string|max:255',
            'graduate_certification_credential_url' => 'nullable|string|max:255',
        ]);

        $certification = Certification::findOrFail($id);
        $certification->update($request->all());

        return Redirect()->back();    }

    // Remove certification
    public function removeCertification($id)
    {
        $certification = Certification::findOrFail($id);
        $certification->delete();

        return Redirect()->back()->with('flash.banner', 'Certification removed successfully.');
    }

    // Add achievement
    public function addAchievement(Request $request)
    {
        $request->validate([
            'graduate_achievement_title' => 'required|string|max:255',
            'graduate_achievement_issuer' => 'required|string|max:255',
            'graduate_achievement_date' => 'required|date',
            'graduate_achievement_description' => 'nullable|string',
            'graduate_achievement_url' => 'nullable|string|max:255',
            'graduate_achievement_type' => 'required|string|in:Award,Recognition,Publication,Patent,Other',
            'credential_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);
    
        $achievement = new Achievement($request->all());
        $achievement->user_id = Auth::id();
        $achievement->graduate_achievement_description = $request->graduate_achievement_description ?? 'No description provided';
        $achievement->graduate_achievement_date = Carbon::parse($request->graduate_achievement_date)->format('Y-m-d');
        
        if ($request->hasFile('credential_picture')) {
            $file = $request->file('credential_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('achievements', $filename, 'public');
            $achievement->credential_picture_url = $path;
        }
    
        $achievement->save();
    
        return Redirect()->back()->with('flash.banner', 'Achievement added successfully.');
    }
    
    public function updateAchievement(Request $request, $id)
    {
        $request->validate([
            'graduate_achievement_title' => 'required|string|max:255',
            'graduate_achievement_issuer' => 'required|string|max:255',
            'graduate_achievement_date' => 'required|date',
            'graduate_achievement_description' => 'nullable|string',
            'graduate_achievement_url' => 'nullable|string|max:255',
            'graduate_achievement_type' => 'required|string|in:Award,Recognition,Publication,Patent,Other',
            'credential_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);
    
        $achievement = Achievement::findOrFail($id);
        
        // Check if achievement belongs to authenticated user
        if ($achievement->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        $achievement->graduate_achievement_title = $request->graduate_achievement_title;
        $achievement->graduate_achievement_issuer = $request->graduate_achievement_issuer;
        $achievement->graduate_achievement_date = Carbon::parse($request->graduate_achievement_date)->format('Y-m-d');
        $achievement->graduate_achievement_description = $request->graduate_achievement_description;
        $achievement->graduate_achievement_url = $request->graduate_achievement_url;
        $achievement->graduate_achievement_type = $request->graduate_achievement_type;
    
        if ($request->hasFile('credential_picture')) {
            // Delete old picture if exists
            if ($achievement->credential_picture_url) {
                Storage::disk('public')->delete($achievement->credential_picture_url);
            }
            
            $file = $request->file('credential_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('achievements', $filename, 'public');
            $achievement->credential_picture_url = $path;
        }
    
        $achievement->save();
    
        return redirect()->back()->with('flash.banner', 'Achievement updated successfully.');
    }

    // Remove achievement
    public function deleteAchievement($id)
    {
        try {
            $achievement = Achievement::findOrFail($id);
            
            // Delete the associated credential picture if it exists
            if ($achievement->credential_picture_url) {
                Storage::delete('public/' . $achievement->credential_picture_url);
            }
            
            $achievement->delete();
            
            return response()->json(['message' => 'Achievement deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete achievement'], 500);
        }
    }

    // Add testimonial
    public function addTestimonial(Request $request)
    {
        $request->validate([
            'graduate_testimonials_name' => 'required|string|max:255',
            'graduate_testimonials_role_title' => 'required|string|max:255',
            'graduate_testimonials_testimonial' => 'required|string',
        ]);

        $testimonial = new Testimonial($request->all());
        $testimonial->user_id = Auth::id(); // Assuming you have a user_id field
        $testimonial->save();

        return Redirect()->back()->with('flash.banner', 'Testimonial added successfully.');
    }

    // Update testimonial
    public function updateTestimonial(Request $request, $id)
    {
        $request->validate([
            'graduate_testimonials_name' => 'required|string|max:255',
            'graduate_testimonials_role_title' => 'required|string|max:255',
            'graduate_testimonials_testimonial' => 'required|string',
        ]);

        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update($request->all());

        return Redirect()->back();    
    }

    // Remove testimonial
    public function removeTestimonial($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->delete();

        return Redirect()->back()->with('flash.banner', 'Testimonial removed successfully.');
    }

    // Update employment preferences
    public function getEmploymentReference()
    {
        $user = Auth::user();
        $employmentReference = $user->employmentPreferences()->first();

        if ($employmentReference) {
            return response()->json([
                'jobTypes' => $employmentReference->jobTypes,
                'salaryExpectations' => $employmentReference->salaryExpectations,
                'preferredLocations' => $employmentReference->preferredLocations,
                'workEnvironment' => $employmentReference->workEnvironment,
                'availability' => $employmentReference->availability,
                'additionalNotes' => $employmentReference->additionalNotes,
            ]);
        }

        return response()->json(null);
    }

    // Save employment preferences
    public function saveEmploymentPreferences(Request $request)
    {
        Log::info('Saving Employment Preferences:', $request->all()); 

        $request->validate([
            'jobTypes' => 'nullable|string',
            'salaryExpectations' => 'nullable|string',
            'preferredLocations' => 'nullable|string',
            'workEnvironment' => 'nullable|string',
            'availability' => 'nullable|string',
            'additionalNotes' => 'nullable|string',
        ]);

        $user = Auth::user();

        $employmentPreferences = $user->employmentPreferences()->firstOrNew([]);

        $employmentPreferences->jobTypes = $request->jobTypes;
        $employmentPreferences->salaryExpectations = $request->salaryExpectations;
        $employmentPreferences->preferredLocations = $request->preferredLocations;
        $employmentPreferences->workEnvironment = $request->workEnvironment;
        $employmentPreferences->availability = $request->availability;
        $employmentPreferences->additionalNotes = $request->additionalNotes;

        $employmentPreferences->save();

        Log::info('Employment Preferences Saved:', $employmentPreferences->toArray());

        return Redirect()->back()->with('flash.banner', 'Employment reference saved successfully.');
    }

    // Save career goals
    public function saveCareerGoals(Request $request)
    {
        Log::info('Saving Career Goals:', $request->all()); // Log the request data
        $request->validate([
            'shortTermGoals' => 'nullable|string',
            'longTermGoals' => 'nullable|string',
            'industriesOfInterest' => 'required|array',
            'careerPath' => 'nullable|string',
        ]);

        $user = Auth::user();
        $careerGoals = $user->careerGoals()->firstOrNew([]);
        $careerGoals->shortTermGoals = $request->shortTermGoals;
        $careerGoals->longTermGoals = $request->longTermGoals;
        $careerGoals->industriesOfInterest = implode(',', $request->industriesOfInterest);
        $careerGoals->careerPath = $request->careerPath;
        $careerGoals->save();

        Log::info('Career Goals Saved:', $careerGoals->toArray()); // Log the saved data

        return Redirect()->back()->with('flash.banner', 'Career goals saved successfully.');
    }

    public function addIndustry(Request $request)
    {
        $request->validate([
            'industriesOfInterest' => 'required|array',
        ]);

        $user = Auth::user();
        $careerGoals = $user->careerGoals()->firstOrNew([]);
        $careerGoals->industriesOfInterest = implode(',', $request->industriesOfInterest);
        $careerGoals->save();

        return Redirect()->back()->with('flash.banner', 'Industry added successfully!');
    }

        public function getCareerGoals()
    {
        $user = Auth::user();
        $careerGoals = $user->careerGoals()->first();

        return response()->json($careerGoals);
    }

    // Upload resume
    public function uploadResume(Request $request)
    {
        $request->validate([
            'resume' => 'required|mimes:pdf,doc,docx|max:2048'
        ]);
    
        try {
            $user = Auth::user();
            $file = $request->file('resume');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('resumes', $fileName, 'public');
    
            // Delete old resume if exists
            if ($user->resume) {
                Storage::disk('public')->delete($user->resume->file);
                $user->resume->delete();
            }
    
            // Create new resume record
            $user->resume()->create([
                'file' => $path,
                'fileName' => $file->getClientOriginalName()
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Resume uploaded successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Resume upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload resume'
            ], 500);
        }
    }
    
    public function deleteResume()
    {
        try {
            $user = Auth::user();
            if ($user->resume) {
                Storage::disk('public')->delete($user->resume->file);
                $user->resume->delete();
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,png,pdf|max:2048',
        ]);

        $path = $request->file('file')->store('uploads', 'public');

        return response()->json(['path' => $path], 201);
    }

    public function getFile($filename)
    {
        $path = storage_path('app/public/uploads/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}