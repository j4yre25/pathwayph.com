<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\EducationUpdateRequest;
use App\Http\Requests\SkillUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\Education;
use App\Models\Skill;
use App\Models\GraduateSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Experience;
use App\Models\Certification;
use App\Models\Achievement;
use App\Models\CareerGoal;
use App\Models\EmploymentPreference;
use App\Models\Institution;
use App\Models\Testimonial;
use App\Models\Project;
use App\Models\Resume;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    // Show the profile settings page
    public function index()
    {
        $user = Auth::user();
        $graduates = \App\Models\Graduate::with(['program.degree', 'schoolYear', 'education', 'institution'])
            ->where('user_id', $user->id)
            ->first();

        // Fetch all institution users (adjust as needed)
        $instiUsers = User::where('role', 'institution')->get();

        // Remove with('institution') here
        $educationEntries = Education::where('graduate_id', $graduates->id)
            ->whereNull('deleted_at')
            ->get();

        return Inertia::render('Frontend/Profile', [
            'user' => $user,
            'graduate' => $graduates,
            'instiUsers' => $instiUsers,
            'educationEntries' => $educationEntries,
            'experienceEntries' => Experience::where('graduate_id', $graduates->id)->get(),
            'skillEntries' => GraduateSkill::with('skill')
                ->where('graduate_id', $graduates->id)
                ->get()
                ->map(function ($gs) {
                    return [
                        'id' => $gs->id,
                        'graduate_skills_name' => $gs->skill ? $gs->skill->name : null,
                        'graduate_skills_proficiency_type' => $gs->proficiency_type,
                        'graduate_skills_type' => $gs->type,
                        'graduate_skills_years_experience' => $gs->years_experience,
                    ];
                }),
            'certificationsEntries' => Certification::where('graduate_id', $graduates->id)->get(),
            'projectsEntries' => Project::where('graduate_id', $graduates->id)->get(),
            'achievementEntries' => Achievement::where('graduate_id', $graduates->id)->get(),
            'testimonialsEntries' => Testimonial::where('graduate_id', $graduates->id)->get(),
            'employmentPreferences' => EmploymentPreference::where('graduate_id', $graduates->id)->first(),
            'careerGoals' => CareerGoal::where('graduate_id', $graduates->id)->first(),
            'resume' => Resume::where('graduate_id', $graduates->id)->first(),
        ]);
    }

    public function educationSettings()
    {
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        // Remove with('institution') here
        $educationEntries = Education::where('graduate_id', $graduate->id)
            ->whereNull('deleted_at')
            ->get();

        $archivedEducationEntries = Education::where('graduate_id', $graduate->id)
            ->whereNotNull('deleted_at')
            ->get();

        $institutions = Institution::select('id', 'institution_name')->get();

        return Inertia::render('Frontend/ProfileSettings/Education', [
            'user' => $user,
            'educationEntries' => $educationEntries,
            'archivedEducationEntries' => $archivedEducationEntries,
            'institutions' => $institutions,
            // Add other props as needed
        ]);
    }

    public function experienceSettings()
    {
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        // Fetch all experience entries for this graduate
        $experienceEntries = Experience::where('graduate_id', $graduate->id)
            ->whereNull('deleted_at') // if you use soft deletes
            ->get();

        // Optionally, fetch archived experience entries if you support archiving
        $archivedExperienceEntries = Experience::where('graduate_id', $graduate->id)
            ->whereNotNull('deleted_at')
            ->get();

        return Inertia::render('Frontend/ProfileSettings/Experience', [
            'user' => $user,
            'experienceEntries' => $experienceEntries,
            'archivedExperienceEntries' => $archivedExperienceEntries,
            // Add other props as needed
        ]);
    }

    public function projectSettings()
    {
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        // Fetch all project entries for this graduate
        $projectsEntries = \App\Models\Project::where('graduate_id', $graduate->id)
            ->whereNull('deleted_at') // if you use soft deletes
            ->get();

        // Optionally, fetch archived project entries if you support archiving
        $archivedProjectsEntries = \App\Models\Project::where('graduate_id', $graduate->id)
            ->whereNotNull('deleted_at')
            ->get();

        return Inertia::render('Frontend/ProfileSettings/Project', [
            'user' => $user,
            'projectsEntries' => $projectsEntries,
            'archivedProjectsEntries' => $archivedProjectsEntries,
            // Add other props as needed
        ]);
    }

    // Update profile information
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:1',
            'last_name' => 'required|string|max:255',
            'current_job_title' => 'required|string|max:255',
            'employment_status' => 'required|in:Employed,Underemployed,Unemployed',
            'current_job_title' => 'nullable|string|max:255',
            'graduate_location' => 'nullable|string|max:255',
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



        // Handle profile picture upload
        if ($request->hasFile('graduate_picture')) {
            $file = $request->file('graduate_picture');
            $originalName = $file->getClientOriginalName();
            if (!$originalName) {
                $originalName = uniqid('profile_', true) . '.' . $file->getClientOriginalExtension();
            }
            $filename = time() . '_' . $originalName;
            $path = $file->storeAs('profile_pictures', $filename, 'public');
            $user->profile_picture = $path;

            // Also update graduates table with the picture path
            DB::table('graduates')
                ->where('user_id', $user->id)
                ->update([
                    'graduate_picture' => $path,
                ]);
        }

        $user->save();

        // Sync to graduates table
        DB::table('graduates')
            ->where('user_id', $user->id)
            ->update([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'dob' => $request->dob,
                'current_job_title' => $request->graduate_professional_title,
                'employment_status' => $request->employment_status,
                'location' => $request->graduate_location,
                'ethnicity' => $request->graduate_ethnicity,
                'address' => $request->graduate_address,
                'about_me' => $request->graduate_about_me,
                'linkedin_url' => $request->linkedin_url,
                'github_url' => $request->github_url,
                'personal_website' => $request->personal_website,
                'other_social_links' => $request->other_social_links,
            ]);
    }



    // Add education
    public function addEducation(Request $request)
    {
        $user = Auth::user();

        $graduates = \App\Models\Graduate::where('user_id', $user->id)->first();


        $request->validate([
            'program' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'is_current' => 'boolean',
            'achievements' => 'nullable|string',
            'no_achievements' => 'nullable|boolean',
        ]);

        $graduates = \App\Models\Graduate::where('user_id', Auth::id())->first();


        $data = $request->all();
        if ($request->is_current) {
            $data['end_date'] = null;
        }

        $education = new Education();
        $education->graduate_id = $graduates->id;
        $education->education = $request->input('education');
        $education->program = $request->input('program'); // <-- add this line
        $education->field_of_study = $request->input('field_of_study');
        $education->start_date = $request->input('start_date');
        $education->end_date = $request->input('end_date');
        $education->description = $request->input('description');
        $education->is_current = $request->input('is_current', false);
        $education->achievements = $request->input('achievements');
        $education->save();

        return redirect()->back()->with('flash.banner', 'Education added successfully.');
    }

    // Update education
    public function updateEducation(Request $request, $id)
    {
        $request->validate([
            'program' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'is_current' => 'boolean',
            'achievements' => 'nullable|string',
            'no_achievements' => 'nullable|boolean',
        ]);

        $data = $request->all();

        if ($request->is_current) {
            $data['end_date'] = null;
        } elseif (isset($data['end_date'])) {
            $data['end_date'] = Carbon::parse($data['end_date'])->format('Y-m-d');
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

        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        $startDate = $request->graduate_experience_start_date
            ? \Carbon\Carbon::parse($request->graduate_experience_start_date)->format('Y-m-d')
            : null;
        $endDate = $request->is_current
            ? null
            : ($request->graduate_experience_end_date
                ? \Carbon\Carbon::parse($request->graduate_experience_end_date)->format('Y-m-d')
                : null);

        // Map request fields to DB columns
        $experience = new \App\Models\Experience();
        $experience->graduate_id = $graduate->id;
        $experience->title = $request->graduate_experience_title;
        $experience->company = $request->graduate_experience_company;
        $experience->start_date = $startDate;
        $experience->end_date = $endDate;
        $experience->address = $request->graduate_experience_address;
        $experience->description = $request->graduate_experience_description ?? 'No description provided';
        $experience->employment_type = $request->graduate_experience_employment_type;
        $experience->save();

        // Return response as needed
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

        $experience = Experience::findOrFail($id);

        $experience->title = $request->graduate_experience_title;
        $experience->company = $request->graduate_experience_company;
        $experience->start_date = $request->graduate_experience_start_date
            ? \Carbon\Carbon::parse($request->graduate_experience_start_date)->format('Y-m-d')
            : null;
        $experience->end_date = $request->is_current
            ? null
            : ($request->graduate_experience_end_date
                ? \Carbon\Carbon::parse($request->graduate_experience_end_date)->format('Y-m-d')
                : null);
        $experience->address = $request->graduate_experience_address;
        $experience->description = $request->graduate_experience_description ?? 'No description provided';
        $experience->employment_type = $request->graduate_experience_employment_type;
        $experience->save();

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
        $request->validate([
            'graduate_skills_name' => 'required|string|max:255',
            'graduate_skills_proficiency_type' => 'required|string|in:Beginner,Intermediate,Advanced,Expert',
            'graduate_skills_type' => 'required|string|max:255',
            'graduate_skills_years_experience' => 'required|integer|min:0',
        ]);

        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->firstOrFail();

        // Check if the skill exists globally (case-insensitive)
        $rawName = strtolower(trim($request->graduate_skills_name));
        $formattedName = \Illuminate\Support\Str::title($rawName);

        $skill = Skill::withTrashed()->whereRaw('LOWER(name) = ?', [$rawName])->first();

        if (!$skill) {
            $skill = Skill::create(['name' => $formattedName]);
        }

        // Check if the graduate already has this skill
        $exists = GraduateSkill::where('graduate_id', $graduate->id)
            ->where('skill_id', $skill->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['flash.banner' => 'You already have this skill.']);
        }

        // Add to graduate_skills
        GraduateSkill::create([
            'graduate_id' => $graduate->id,
            'skill_id' => $skill->id,
            'proficiency_type' => $request->graduate_skills_proficiency_type,
            'type' => $request->graduate_skills_type,
            'years_experience' => $request->graduate_skills_years_experience,
        ]);

        return redirect()->back()->with('flash.banner', 'Skill added successfully.');
    }

    // Update skill
    public function updateSkill(Request $request, $id)
    {
        $request->validate([
            'graduate_skills_name' => 'required|string|max:255',
            'graduate_skills_proficiency_type' => 'required|string|in:Beginner,Intermediate,Advanced,Expert',
            'graduate_skills_type' => 'required|string|max:255',
            'graduate_skills_years_experience' => 'required|integer|min:0',
        ]);

        $graduateSkill = GraduateSkill::findOrFail($id);

        // Update the skill name in the global skills table if needed
        $rawName = strtolower(trim($request->graduate_skills_name));
        $formattedName = \Illuminate\Support\Str::title($rawName);
        $skill = Skill::whereRaw('LOWER(name) = ?', [$rawName])->first();
        if (!$skill) {
            $skill = Skill::create(['name' => $formattedName]);
        }
        $graduateSkill->skill_id = $skill->id;
        $graduateSkill->proficiency_type = $request->graduate_skills_proficiency_type;
        $graduateSkill->type = $request->graduate_skills_type;
        $graduateSkill->years_experience = $request->graduate_skills_years_experience;
        $graduateSkill->save();

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
        $request->validate([
            'graduate_projects_title' => 'required|string|max:255',
            'graduate_projects_description' => 'nullable|string',
            'graduate_projects_role' => 'required|string|max:255',
            'graduate_projects_start_date' => 'required|date',
            'graduate_projects_end_date' => 'nullable|date',
            'graduate_projects_url' => 'nullable|string|max:255',
            'graduate_projects_key_accomplishments' => 'nullable|string',
            'graduate_project_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'is_current' => 'boolean'
        ]);

        // Map request fields to DB columns
        $data = [
            'title' => $request->graduate_projects_title,
            'description' => $request->graduate_projects_description ?? 'No description provided',
            'role' => $request->graduate_projects_role,
            'start_date' => \Carbon\Carbon::parse($request->graduate_projects_start_date)->format('Y-m-d'),
            'end_date' => $request->is_current ? null : (
                $request->graduate_projects_end_date
                ? \Carbon\Carbon::parse($request->graduate_projects_end_date)->format('Y-m-d')
                : null
            ),
            'url' => $request->graduate_projects_url,
            'key_accomplishments' => $request->graduate_projects_key_accomplishments,
        ];

        // Handle file upload
        if ($request->hasFile('graduate_project_file')) {
            $file = $request->file('graduate_project_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('project_files', $filename, 'public');
            $data['file'] = $path;
        }

        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();
        $data['graduate_id'] = $graduate->id;

        $project = new Project($data);
        $project->save();

        // Fetch updated projects
        $projectsEntries = Project::where('graduate_id', $graduate->id)
            ->whereNull('deleted_at')
            ->get();
        $archivedProjectsEntries = Project::where('graduate_id', $graduate->id)
            ->whereNotNull('deleted_at')
            ->get();

        return redirect()->back()->with('flash.banner', 'Project added successfully.');
    }

    public function updateProject(Request $request, $id)
    {
        try {
            $project = Project::findOrFail($id);


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

            $user = Auth::user();
            $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();
            $data['graduate_id'] = $graduate->id;

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

    public function certificationSettings()
    {
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        $certificationsEntries = Certification::where('graduate_id', $graduate->id)
            ->whereNull('deleted_at')
            ->get();

        $archivedCertificationsEntries = Certification::where('graduate_id', $graduate->id)
            ->whereNotNull('deleted_at')
            ->get();

        return Inertia::render('Frontend/ProfileSettings/Certification', [
            'user' => $user,
            'certificationsEntries' => $certificationsEntries,
            'archivedCertificationsEntries' => $archivedCertificationsEntries,
        ]);
    }


    // Add certification
    public function addCertification(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'issuer' => 'required|string|max:255',
                'issue_date' => 'required|date',
                'expiry_date' => 'nullable|date',
                'credential_url' => 'nullable|string|max:255',
                'credential_id' => 'nullable|string|max:255',
                'noExpiryDate' => 'boolean',
                'noCredentialUrl' => 'boolean',
                'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);

            $user = Auth::user();
            $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

            $data = [
                'graduate_id' => $graduate->id,
                'name' => $request->name,
                'issuer' => $request->issuer,
                'issue_date' => \Carbon\Carbon::parse($request->issue_date)->format('Y-m-d'),
                'expiry_date' => $request->noExpiryDate ? null : (
                    $request->expiry_date
                    ? \Carbon\Carbon::parse($request->expiry_date)->format('Y-m-d')
                    : null
                ),
                'credential_url' => $request->noCredentialUrl ? null : $request->credential_url,
                'credential_id' => $request->credential_id,
            ];

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('certification_files', $filename, 'public');
                $data['file_path'] = $path;
            }

            $certification = new Certification($data);
            $certification->save();

            return redirect()->back()->with('flash.banner', 'Certification added successfully.');
        } catch (\Exception $e) {
            Log::error('Error adding certification: ' . $e->getMessage());
            return redirect()->back()->with('flash.banner', 'Failed to add certification. Please try again.');
        }
    }

    public function updateCertification(Request $request, $id)
    {
        try {
            $certification = Certification::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'issuer' => 'required|string|max:255',
                'issue_date' => 'required|date',
                'expiry_date' => 'nullable|date',
                'credential_url' => 'nullable|string|max:255',
                'credential_id' => 'nullable|string|max:255',
                'noExpiryDate' => 'boolean',
                'noCredentialUrl' => 'boolean',
                'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);

            $data = [
                'name' => $request->name,
                'issuer' => $request->issuer,
                'issue_date' => \Carbon\Carbon::parse($request->issue_date)->format('Y-m-d'),
                'expiry_date' => $request->noExpiryDate ? null : (
                    $request->expiry_date
                    ? \Carbon\Carbon::parse($request->expiry_date)->format('Y-m-d')
                    : null
                ),
                'credential_url' => $request->noCredentialUrl ? null : $request->credential_url,
                'credential_id' => $request->credential_id,
            ];

            // Handle file upload
            if ($request->hasFile('file')) {
                // Optionally delete old file
                if ($certification->file_path) {
                    Storage::disk('public')->delete($certification->file_path);
                }
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('certification_files', $filename, 'public');
                $data['file_path'] = $path;
            }

            $certification->update($data);

            return redirect()->back()->with('flash.banner', 'Certification updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating certification: ' . $e->getMessage());
            return redirect()->back()->with('flash.banner', 'Failed to update certification. Please try again.');
        }
    }
    public function removeCertification($id)
    {
        try {
            $certification = Certification::findOrFail($id);

            if ($certification->user_id !== Auth::id()) {
                return redirect()->back()->with('flash.banner', 'Unauthorized access.');
            }

            // Optionally delete file
            if ($certification->file_path) {
                Storage::disk('public')->delete($certification->file_path);
            }

            $certification->delete();

            return redirect()->back()->with('flash.banner', 'Certification removed successfully.');
        } catch (\Exception $e) {
            Log::error('Error removing certification: ' . $e->getMessage());
            return redirect()->back()->with('flash.banner', 'Failed to remove certification. Please try again.');
        }
    }

    public function achievementSettings()
    {
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        // Fetch all achievement entries for this graduate
        $achievementEntries = Achievement::where('graduate_id', $graduate->id)
            ->whereNull('deleted_at') // if you use soft deletes
            ->get();

        // Optionally, fetch archived achievement entries if you support archiving
        $archivedAchievementEntries = Achievement::where('graduate_id', $graduate->id)
            ->whereNotNull('deleted_at')
            ->get();

        return Inertia::render('Frontend/ProfileSettings/Achievement', [
            'user' => $user,
            'achievementEntries' => $achievementEntries,
            'archivedAchievementEntries' => $archivedAchievementEntries,
            // Add other props as needed
        ]);
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

        $graduate = \App\Models\Graduate::where('user_id', Auth::id())->firstOrFail();

        $achievement = new Achievement();
        $achievement->graduate_id = $graduate->id;
        $achievement->title = $request->graduate_achievement_title;
        $achievement->issuer = $request->graduate_achievement_issuer;
        $achievement->date = \Carbon\Carbon::parse($request->graduate_achievement_date)->format('Y-m-d');
        $achievement->description = $request->graduate_achievement_description ?? 'No description provided';
        $achievement->url = $request->graduate_achievement_url;
        $achievement->type = $request->graduate_achievement_type;

        if ($request->hasFile('credential_picture')) {
            $file = $request->file('credential_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('achievements', $filename, 'public');
            $achievement->credential_picture = $path;
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

        $graduate = \App\Models\Graduate::where('user_id', Auth::id())->firstOrFail();
        $achievement = Achievement::where('id', $id)->where('graduate_id', $graduate->id)->firstOrFail();

        $achievement->title = $request->graduate_achievement_title;
        $achievement->issuer = $request->graduate_achievement_issuer;
        $achievement->date = \Carbon\Carbon::parse($request->graduate_achievement_date)->format('Y-m-d');
        $achievement->description = $request->graduate_achievement_description;
        $achievement->url = $request->graduate_achievement_url;
        $achievement->type = $request->graduate_achievement_type;

        if ($request->hasFile('credential_picture')) {
            if ($achievement->credential_picture) {
                Storage::disk('public')->delete($achievement->credential_picture);
            }
            $file = $request->file('credential_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('achievements', $filename, 'public');
            $achievement->credential_picture = $path;
        }

        $achievement->save();

        return redirect()->back()->with('flash.banner', 'Achievement updated successfully.');
    }
    // Remove achievement
    public function deleteAchievement($id)
    {
        try {
            $achievement = Achievement::findOrFail($id);

            $graduate = \App\Models\Graduate::where('user_id', Auth::id())->firstOrFail();
            $achievement = Achievement::where('id', $id)->where('graduate_id', $graduate->id)->firstOrFail();
            // Check ownership
            if ($achievement->user_id !== Auth::id() || $achievement->graduate_id !== $graduate->id) {
                return redirect()->back()->with('flash.banner', 'Unauthorized access.');
            }

            if ($achievement->credential_picture_url) {
                Storage::disk('public')->delete($achievement->credential_picture_url);
            }

            $achievement->delete();

            return redirect()->back()->with('flash.banner', 'Achievement removed successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting achievement: ' . $e->getMessage());
            return redirect()->back()->with('flash.banner', 'Failed to remove achievement. Please try again.');
        }
    }
    public function testimonialSettings()
    {
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        // Fetch all testimonial entries for this graduate
        $testimonialsEntries = Testimonial::where('graduate_id', $graduate->id)
            ->whereNull('deleted_at') // if you use soft deletes
            ->get();

        // Optionally, fetch archived testimonial entries if you support archiving
        $archivedTestimonialsEntries = Testimonial::where('graduate_id', $graduate->id)
            ->whereNotNull('deleted_at')
            ->get();

        return Inertia::render('Frontend/ProfileSettings/Testimonial', [
            'user' => $user,
            'testimonialsEntries' => $testimonialsEntries,
            'archivedTestimonialsEntries' => $archivedTestimonialsEntries,
        ]);
    }
    // Add testimonial
    public function addTestimonial(Request $request)
    {
        $request->validate([
            'author' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'content' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        $data = [
            'author' => $request->author,
            'position' => $request->position,
            'company' => $request->company,
            'content' => $request->content,
            'graduate_id' => $graduate->id,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('testimonials', $filename, 'public');
            $data['file'] = $path;
        }

        $testimonial = new Testimonial($data);
        $testimonial->save();

        return redirect()->back()->with('flash.banner', 'Testimonial added successfully.');
    }

    // Update testimonial
    public function updateTestimonial(Request $request, $id)
    {
        $request->validate([
            'author' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'content' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();
        $testimonial = Testimonial::where('id', $id)->where('graduate_id', $graduate->id)->firstOrFail();

        $testimonial->author = $request->author;
        $testimonial->position = $request->position;
        $testimonial->company = $request->company;
        $testimonial->content = $request->content;

        if ($request->hasFile('file')) {
            if ($testimonial->file) {
                Storage::disk('public')->delete($testimonial->file);
            }
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('testimonials', $filename, 'public');
            $testimonial->file = $path;
        }

        $testimonial->save();

        return redirect()->back()->with('flash.banner', 'Testimonial updated successfully.');
    }

    public function removeTestimonial($id)
    {
        try {
            $user = Auth::user();
            $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();
            $testimonial = Testimonial::where('id', $id)->where('graduate_id', $graduate->id)->firstOrFail();

            if ($testimonial->file) {
                Storage::disk('public')->delete($testimonial->file);
            }

            $testimonial->delete();

            return redirect()->back()->with('flash.banner', 'Testimonial removed successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting testimonial: ' . $e->getMessage());
            return redirect()->back()->with('flash.banner', 'Failed to remove testimonial. Please try again.');
        }
    }

    public function employmentReferenceSettings()
    {
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        $employmentPreferences = \App\Models\EmploymentPreference::where('graduate_id', $graduate->id)->first();

        return Inertia::render('Frontend/ProfileSettings/Employment', [
            'user' => $user,
            'employmentPreferences' => $employmentPreferences,
        ]);
    }

    // Update employment preferences
    public function getEmploymentReference()
    {
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();
        $employmentReference = \App\Models\EmploymentPreference::where('graduate_id', $graduate->id)->first();

        return response()->json($employmentReference);
    }

    // Save employment preferences
    public function saveEmploymentReference(Request $request)
    {
        $request->validate([
            'job_types' => 'nullable|string',
            'salary_expectations' => 'nullable|string',
            'preferred_locations' => 'nullable|string',
            'work_environment' => 'nullable|string',
            'availability' => 'nullable|string',
            'additional_notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        $employmentReference = \App\Models\EmploymentPreference::firstOrNew([
            'graduate_id' => $graduate->id
        ]);

        $employmentReference->job_types = $request->job_types;
        $employmentReference->salary_expectations = $request->salary_expectations;
        $employmentReference->preferred_locations = $request->preferred_locations;
        $employmentReference->work_environment = $request->work_environment;
        $employmentReference->availability = $request->availability;
        $employmentReference->graduate_id = $graduate->id;

        $employmentReference->save();

        return redirect()->back()->with('flash.banner', 'Employment reference saved successfully.');
    }

    // Save career goals
    public function saveCareerGoals(Request $request)
    {
        $request->validate([
            'short_term_goals' => 'nullable|string',
            'long_term_goals' => 'nullable|string',
            'industries_of_interest' => 'nullable|string', // Accept as comma-separated string
            'career_path' => 'nullable|string',
        ]);

        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        $careerGoals = \App\Models\CareerGoal::firstOrNew([
            'graduate_id' => $graduate->id
        ]);

        $careerGoals->short_term_goals = $request->short_term_goals;
        $careerGoals->long_term_goals = $request->long_term_goals;
        $careerGoals->industries_of_interest = $request->industries_of_interest;
        $careerGoals->career_path = $request->career_path;
        $careerGoals->graduate_id = $graduate->id;

        $careerGoals->save();

        return redirect()->back()->with('flash.banner', 'Career goals saved successfully!');
    }

    public function addIndustry(Request $request)
    {
        $request->validate([
            'industries_of_interest' => 'required|array',
        ]);

        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->firstOrFail();

        $careerGoals = \App\Models\CareerGoal::firstOrNew([
            'graduate_id' => $graduate->id
        ]);

        $careerGoals->industries_of_interest = implode(',', $request->industries_of_interest);
        $careerGoals->graduate_id = $graduate->id;
        $careerGoals->save();

        return redirect()->back()->with('flash.banner', 'Industry added successfully!');
    }

    public function getCareerGoals()
    {
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();
        $careerGoals = \App\Models\CareerGoal::where('graduate_id', $graduate->id)->first();

        return response()->json($careerGoals);
    }

    public function uploadResume(Request $request)
    {
        $request->validate([
            'resume' => 'required|mimes:pdf,doc,docx|max:2048',
        ]);

        try {
            $user = Auth::user();
            $graduate = \App\Models\Graduate::where('user_id', $user->id)->firstOrFail();

            $file = $request->file('resume');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $fileType = $file->getClientMimeType();
            $fileSize = $file->getSize();
            $path = $file->storeAs('resumes', $fileName, 'public');

            // Optionally delete old file
            $oldResume = \App\Models\Resume::where('graduate_id', $graduate->id)->first();
            if ($oldResume && $oldResume->file_path) {
                Storage::disk('public')->delete($oldResume->file_path);
            }

            $resume = \App\Models\Resume::updateOrCreate(
                ['graduate_id' => $graduate->id],
                [
                    'user_id' => $user->id,
                    'title' => $request->input('title', 'Resume'),
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $fileType,
                    'file_size' => $fileSize,
                    'is_primary' => true,
                ]
            );

            // Return JSON for AJAX (Inertia) requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['resume' => $resume]);
            }

            // Fallback for non-AJAX
            return redirect()->back()->with('success', 'Resume uploaded successfully!');
        } catch (\Exception $e) {
            Log::error('Resume upload error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to upload resume'], 500);
        }
    }

    public function deleteResume()
    {
        try {
            $user = Auth::user();
            $graduate = \App\Models\Graduate::where('user_id', $user->id)->firstOrFail();
            $resume = Resume::where('graduate_id', $graduate->id)->first();

            if ($resume && $resume->file_path) {
                Storage::disk('public')->delete($resume->file_path);
                $resume->delete();
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    public function resumeSettings()
    {
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();
        $resume = \App\Models\Resume::where('graduate_id', $graduate->id)->first();

        return Inertia::render('Frontend/ProfileSettings/Resume', [
            'user' => $user,
            'resume' => $resume,
        ]);
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
