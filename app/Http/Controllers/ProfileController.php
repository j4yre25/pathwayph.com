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
use App\Models\Resume;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // Show the profile settings page
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Frontend/Profile', [
            'user' => $user,
            'educationEntries' => Education::where('user_id', $user->id)->get(),
            'experienceEntries' => Experience::where('user_id', $user->id)->get(),
            'skillEntries' => Skill::where('user_id', $user->id)->get(),
            'certificationsEntries' => Certification::where('user_id', $user->id)->get(),
            'projectsEntries' => Project::where('user_id', $user->id)->get(),
            'achievementEntries' => Achievement::where('user_id', $user->id)->get(),
            'testimonialsEntries' => Testimonial::where('user_id', $user->id)->get(),
            'employmentPreferences' => EmploymentPreference::where('user_id', $user->id)->first(),
            'careerGoals' => $user->careerGoals,
            'resume' => $user->resume,
        ]);
    }

    // Update profile information
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */

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
            'graduate_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'graduate_about_me' => 'nullable|string|max:1000',
        ]);
        /** @var \App\Models\User $user */

        $user = Auth::user();

        if ($request->hasFile('graduate_picture')) {
            $file = $request->file('graduate_picture');
            $originalName = $file->getClientOriginalName();
            if (!$originalName) {
                // fallback to a unique name if original is empty
                $originalName = uniqid('profile_', true) . '.' . $file->getClientOriginalExtension();
            }
            $filename = time() . '_' . $originalName;
            $path = $file->storeAs('profile_pictures', $filename, 'public');

            $user->profile_picture = $path;
        }

        $user->save();

        return redirect()->back()->with([
            'success' => 'Profile updated!',
            'user' => $user->fresh(),
        ]);
    }

    // Add education
    public function addEducation(Request $request)
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
            'no_achievements' => 'nullable|boolean',
        ]);

        $data = $request->all();
        if ($request->is_current) {
            $data['graduate_education_end_date'] = null;
        }

        $education = new Education($data);
        $education->user_id = Auth::id();
        $education->save();

        return redirect()->back()->with('flash.banner', 'Education added successfully.');
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
            'no_achievements' => 'nullable|boolean',
        ]);

        $data = $request->all();

        if ($request->is_current) {
            $data['graduate_education_end_date'] = null;
        } elseif (isset($data['graduate_education_end_date'])) {
            $data['graduate_education_end_date'] = Carbon::parse($data['graduate_education_end_date'])->format('Y-m-d');
        }

        $education = Education::findOrFail($id);
        $education->update($data);

        return redirect()->back()->with('flash.banner', 'Education updated successfully.');
    }

    // Remove education
    public function removeEducation($id)
    {
        $education = Education::findOrFail($id);
        $education->delete();

        return redirect()->back()->with('flash.banner', 'Education removed successfully.');
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
            'graduate_experience_employment_type' => 'nullable|string|max:255',
            'is_current' => 'boolean',
        ]);

        $data = $request->all();

        if ($request->is_current) {
            $data['graduate_experience_end_date'] = null;
        }

        if (isset($data['graduate_experience_start_date'])) {
            $data['graduate_experience_start_date'] = Carbon::parse($data['graduate_experience_start_date'])->format('Y-m-d H:i:s');
        }

        if (isset($data['graduate_experience_end_date'])) {
            $data['graduate_experience_end_date'] = Carbon::parse($data['graduate_experience_end_date'])->format('Y-m-d H:i:s');
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

        if ($request->is_current) {
            $data['graduate_experience_end_date'] = null;
        }

        if (isset($data['graduate_experience_start_date'])) {
            $data['graduate_experience_start_date'] = Carbon::parse($data['graduate_experience_start_date'])->format('Y-m-d H:i:s');
        }

        if (isset($data['graduate_experience_end_date'])) {
            $data['graduate_experience_end_date'] = Carbon::parse($data['graduate_experience_end_date'])->format('Y-m-d H:i:s');
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

        return redirect()->back()->with('flash.banner', 'Experience removed successfully.');
    }

    // Add skill
    public function addSkill(Request $request)
    {
        Log::info('Request data:', $request->all());

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
            'graduate_skills_proficiency_type' => 'required|string|in:Beginner,Intermediate,Advanced,Expert',
            'graduate_skills_type' => 'required|string|max:255',
            'graduate_skills_years_experience' => 'required|integer|min:0',
        ]);

        $skill = new Skill();
        $skill->graduate_skills_name = $request->graduate_skills_name;
        $skill->graduate_skills_proficiency_type = $request->graduate_skills_proficiency_type;
        $skill->graduate_skills_type = $request->graduate_skills_type;
        $skill->graduate_skills_years_experience = $request->graduate_skills_years_experience;
        $skill->user_id = Auth::id();
        $skill->save();

        return redirect()->back()->with('flash.banner', 'Skill added successfully.');
    }

    // Update skill
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

        return redirect()->back()->with('flash.banner', 'Skill removed successfully.');
    }

    // Add Project
    public function addProject(Request $request)
    {
        try {
            $request->validate([
                'graduate_projects_title' => 'required|string|max:255',
                'graduate_projects_description' => 'nullable|string',
                'graduate_projects_role' => 'required|string|max:255',
                'graduate_projects_start_date' => 'required|date',
                'graduate_projects_end_date' => 'nullable|date',
                'graduate_projects_url' => 'nullable|string|max:255',
                'graduate_projects_key_accomplishments' => 'nullable|string',
                'graduate_project_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // <-- add this line
                'is_current' => 'boolean'
            ]);

            $data = $request->all();

            if ($request->is_current) {
                $data['graduate_projects_end_date'] = null;
            }

            if (isset($data['graduate_projects_start_date'])) {
                $data['graduate_projects_start_date'] = Carbon::parse($data['graduate_projects_start_date'])->format('Y-m-d');
            }

            if (isset($data['graduate_projects_end_date'])) {
                $data['graduate_projects_end_date'] = Carbon::parse($data['graduate_projects_end_date'])->format('Y-m-d');
            }

            $data['graduate_projects_description'] = $data['graduate_projects_description'] ?? 'No description provided';

            // Handle file upload
            if ($request->hasFile('graduate_project_file')) {
                $file = $request->file('graduate_project_file');
                // dd($file);
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('project_files', $filename, 'public');
                $data['graduate_project_file'] = $path;
            }

            $project = new Project($data);
            $project->user_id = Auth::id();
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

            $request->validate([
                'graduate_projects_title' => 'required|string|max:255',
                'graduate_projects_description' => 'nullable|string',
                'graduate_projects_role' => 'required|string|max:255',
                'graduate_projects_start_date' => 'required|date',
                'graduate_projects_end_date' => 'nullable|date',
                'graduate_projects_url' => 'nullable|string|max:255',
                'graduate_projects_key_accomplishments' => 'nullable|string',
                'graduate_project_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // <-- add this line
                'is_current' => 'boolean'
            ]);

            $data = $request->all();

            if ($request->is_current) {
                $data['graduate_projects_end_date'] = null;
            }

            if (isset($data['graduate_projects_start_date'])) {
                $data['graduate_projects_start_date'] = Carbon::parse($data['graduate_projects_start_date'])->format('Y-m-d');
            }

            if (isset($data['graduate_projects_end_date'])) {
                $data['graduate_projects_end_date'] = Carbon::parse($data['graduate_projects_end_date'])->format('Y-m-d');
            }

            $data['graduate_projects_description'] = $data['graduate_projects_description'] ?? 'No description provided';

            // Handle file upload
            if ($request->hasFile('graduate_project_file')) {
                // Optionally delete old file
                if ($project->graduate_project_file) {
                    Storage::disk('public')->delete($project->graduate_project_file);
                }
                $file = $request->file('graduate_project_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('project_files', $filename, 'public');
                $data['graduate_project_file'] = $path;
            }

            $project->update($data);

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
        try {
            $request->validate([
                'graduate_certification_name' => 'required|string|max:255',
                'graduate_certification_issuer' => 'required|string|max:255',
                'graduate_certification_issue_date' => 'required|date',
                'graduate_certification_expiry_date' => 'nullable|date',
                'graduate_certification_credential_id' => 'nullable|string|max:255',
                'graduate_certification_credential_url' => 'nullable|string|max:255',
                'noExpiryDate' => 'boolean',
                'noCredentialUrl' => 'boolean'
            ]);

            $data = $request->all();

            if ($request->noExpiryDate) {
                $data['graduate_certification_expiry_date'] = null;
            }

            if ($request->noCredentialUrl) {
                $data['graduate_certification_credential_url'] = null;
            }

            if (isset($data['graduate_certification_issue_date'])) {
                $data['graduate_certification_issue_date'] = Carbon::parse($data['graduate_certification_issue_date'])->format('Y-m-d');
            }

            if (isset($data['graduate_certification_expiry_date'])) {
                $data['graduate_certification_expiry_date'] = Carbon::parse($data['graduate_certification_expiry_date'])->format('Y-m-d');
            }

            $certification = new Certification($data);
            $certification->user_id = Auth::id();
            $certification->save();

            return redirect()->back()->with('flash.banner', 'Certification added successfully.');
        } catch (\Exception $e) {
            Log::error('Error adding certification: ' . $e->getMessage());
            return redirect()->back()->with('flash.banner', 'Failed to add certification. Please try again.');
        }
    }

    // Update certification
    public function updateCertification(Request $request, $id)
    {
        try {
            $certification = Certification::findOrFail($id);

            if ($certification->user_id !== Auth::id()) {
                return redirect()->back()->with('flash.banner', 'Unauthorized access.');
            }

            $request->validate([
                'graduate_certification_name' => 'required|string|max:255',
                'graduate_certification_issuer' => 'required|string|max:255',
                'graduate_certification_issue_date' => 'required|date',
                'graduate_certification_expiry_date' => 'nullable|date',
                'graduate_certification_credential_id' => 'nullable|string|max:255',
                'graduate_certification_credential_url' => 'nullable|string|max:255',
                'noExpiryDate' => 'boolean',
                'noCredentialUrl' => 'boolean'
            ]);

            $data = $request->all();

            if ($request->noExpiryDate) {
                $data['graduate_certification_expiry_date'] = null;
            }

            if ($request->noCredentialUrl) {
                $data['graduate_certification_credential_url'] = null;
            }

            if (isset($data['graduate_certification_issue_date'])) {
                $data['graduate_certification_issue_date'] = Carbon::parse($data['graduate_certification_issue_date'])->format('Y-m-d');
            }

            if (isset($data['graduate_certification_expiry_date'])) {
                $data['graduate_certification_expiry_date'] = Carbon::parse($data['graduate_certification_expiry_date'])->format('Y-m-d');
            }

            $certification->update($data);

            return redirect()->back()->with('flash.banner', 'Certification updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating certification: ' . $e->getMessage());
            return redirect()->back()->with('flash.banner', 'Failed to update certification. Please try again.');
        }
    }

    // Remove certification
    public function removeCertification($id)
    {
        try {
            $certification = Certification::findOrFail($id);

            if ($certification->user_id !== Auth::id()) {
                return redirect()->back()->with('flash.banner', 'Unauthorized access.');
            }

            $certification->delete();

            return redirect()->back()->with('flash.banner', 'Certification removed successfully.');
        } catch (\Exception $e) {
            Log::error('Error removing certification: ' . $e->getMessage());
            return redirect()->back()->with('flash.banner', 'Failed to remove certification. Please try again.');
        }
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

        return redirect()->back()->with('flash.banner', 'Achievement added successfully.');
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

            if ($achievement->credential_picture_url) {
                Storage::delete('public/' . $achievement->credential_picture_url);
            }

            $achievement->delete();

            return redirect()->back()->with('flash.banner', 'Achievement removed successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting achievement: ' . $e->getMessage());
            return redirect()->back()->with('flash.banner', 'Failed to remove achievement. Please try again.');
        }
    }

    // Add testimonial
    public function addTestimonial(Request $request)
    {
        $request->validate([
            'graduate_testimonials_name' => 'required|string|max:255',
            'graduate_testimonials_role_title' => 'required|string|max:255',
            'graduate_testimonials_testimonial' => 'required|string',
            'graduate_testimonials_letters' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
        ]);

        $data = $request->all();

        if ($request->hasFile('graduate_testimonials_letters')) {
            $path = $request->file('graduate_testimonials_letters')->store('testimonials', 'public');
            $data['graduate_testimonials_letters'] = $path;
        }

        $testimonial = new Testimonial($data);
        $testimonial->user_id = Auth::id();
        $testimonial->save();

        return redirect()->back()->with('flash.banner', 'Testimonial added successfully.');
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

        return redirect()->back()->with('flash.banner', 'Testimonial updated successfully.');
    }

    // Remove testimonial
    public function removeTestimonial($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->delete();

        return redirect()->back()->with('flash.banner', 'Testimonial removed successfully.');
    }

    // Update employment preferences
    public function getEmploymentReference()
    {
        /** @var \App\Models\User $user */

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

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $employmentPreferences = $user->employmentPreferences()->firstOrNew(['user_id' => $user->id]);

        $employmentPreferences->jobTypes = $request->jobTypes;
        $employmentPreferences->salaryExpectations = $request->salaryExpectations;
        $employmentPreferences->preferredLocations = $request->preferredLocations;
        $employmentPreferences->workEnvironment = $request->workEnvironment;
        $employmentPreferences->availability = $request->availability;
        $employmentPreferences->additionalNotes = $request->additionalNotes;

        $employmentPreferences->save();

        Log::info('Employment Preferences Saved:', $employmentPreferences->toArray());

        return redirect()->back()->with('flash.banner', 'Employment reference saved successfully.');
    }

    // Save career goals
    public function saveCareerGoals(Request $request)
    {
        Log::info('Saving Career Goals:', $request->all());
        $request->validate([
            'shortTermGoals' => 'nullable|string',
            'longTermGoals' => 'nullable|string',
            'industriesOfInterest' => 'required|array',
            'careerPath' => 'nullable|string',
        ]);
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $careerGoals = $user->careerGoals()->firstOrNew(['user_id' => $user->id]);
        $careerGoals->shortTermGoals = $request->shortTermGoals;
        $careerGoals->longTermGoals = $request->longTermGoals;
        $careerGoals->industriesOfInterest = implode(',', $request->industriesOfInterest);
        $careerGoals->careerPath = $request->careerPath;
        $careerGoals->save();

        Log::info('Career Goals Saved:', $careerGoals->toArray());

        return redirect()->back()->with('flash.banner', 'Career goals saved successfully.');
    }

    public function addIndustry(Request $request)
    {
        $request->validate([
            'industriesOfInterest' => 'required|array',
        ]);
        /** @var \App\Models\User $user */

        $user = Auth::user();
        $careerGoals = $user->careerGoals()->firstOrNew(['user_id' => $user->id]);
        $careerGoals->industriesOfInterest = implode(',', $request->industriesOfInterest);
        $careerGoals->save();

        return redirect()->back()->with('flash.banner', 'Industry added successfully!');
    }

    public function getCareerGoals()
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $careerGoals = $user->careerGoals()->first();

        return response()->json($careerGoals);
    }

    // Upload resume
   public function uploadResume(Request $request)
{
    $request->validate([
        'resume' => 'required|mimes:pdf,doc,docx|max:2048',
    ]);

    try {
        $user = Auth::user();
        $file = $request->file('resume');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('resumes', $fileName, 'public');

        // Delete old file if exists
        if ($user->resume) {
            Storage::disk('public')->delete($user->resume->file);
        }
        
        /** @var \App\Models\User $user */

        // Save or update resume record
        $user->resume()->updateOrCreate(
            [],
            [
                'file' => $path,
                'fileName' => $file->getClientOriginalName(),
                'user_id' => $user->id
            ]
        );

        return redirect()->back()->with('success', 'Resume uploaded successfully!');
    } catch (\Exception $e) {
        Log::error('Resume upload error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to upload resume');
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
