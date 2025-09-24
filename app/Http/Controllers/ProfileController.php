<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\EducationUpdateRequest;
use App\Http\Requests\SkillUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\Education;
use App\Models\GraduateEducation;
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
use App\Models\JobType;
use App\Models\Salary;
use App\Models\Location;
use Illuminate\Support\Facades\Hash;
use App\Models\WorkEnvironment;
use App\Models\Company;
use App\Models\Sector;

class ProfileController extends Controller
{
    // Show the profile settings page
    public function index()
    {
        $user = Auth::user();
        $graduate = \App\Models\Graduate::with([
            'institution',
            'schoolYear',
            'program.degree'
        ])->where('user_id', $user->id)->first();

        // Eager load company relation for experience entries
        $experienceEntries = Experience::with('company')
            ->where('graduate_id', $graduate->id)
            ->get();
    
        $archivedExperienceEntries = Experience::onlyTrashed()
            ->where('graduate_id', $graduate->id)
            ->get();

        // Fetch all institution users (adjust as needed)
        $instiUsers = User::where('role', 'institution')->get();

        $educationEntries = GraduateEducation::where('graduate_id', $graduate->id)
            ->whereNull('deleted_at')
            ->get();

        $archivedEducationEntries = GraduateEducation::onlyTrashed()
            ->where('graduate_id', $graduate->id)
            ->get();

        $internships = [];
        if ($graduate) {
            $internships = $graduate->internshipPrograms()
                ->with(['programs', 'careerOpportunities'])
                ->get();
        }

        $institutionsRaw = Institution::with([
            'institutionDegrees.degree:id,type,code',
            'institutionPrograms.program:id,name,degree_id',
        ])->get(['id', 'institution_name']);

        $institutions = $institutionsRaw->map(function ($inst) {
            return [
                'id' => $inst->id,
                'name' => $inst->institution_name,
                'degrees' => $inst->institutionDegrees
                    ->filter(fn($idg) => $idg->degree)
                    ->map(fn($idg) => [
                        'id' => $idg->degree->id,
                        'type' => $idg->degree->type,
                        'code' => $idg->degree->code ?? null,
                    ])->unique('id')->values(),
                'programs' => $inst->institutionPrograms
                    ->filter(fn($ip) => $ip->program)
                    ->map(fn($ip) => [
                        'id' => $ip->program->id,
                        'name' => $ip->program->name,
                        'degree_id' => $ip->program->degree_id,
                    ])->values(),
            ];
        })->values();

        $educationLevels = Education::orderBy('order_rank')->get(['id','name','order_rank']);
        $locations = Location::select('id', 'address as name')->orderBy('address')->get();
        $companies = Company::select('id', 'company_name as name')->orderBy('company_name')->get();
        $sectors = Sector::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Frontend/ProfileSettings/ProfileSettings', [
            'user' => $user,
            'graduate' => $graduate, 
            'instiUsers' => $instiUsers,
            'educationEntries' => $educationEntries,
            'archivedEducationEntries' => $archivedEducationEntries,
            'experienceEntries' => $experienceEntries,
            'archivedExperienceEntries' => $archivedExperienceEntries,
            'skillEntries' => GraduateSkill::with('skill')
                ->where('graduate_id', $graduate->id)
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
            'certificationsEntries' => Certification::where('graduate_id', $graduate->id)->get(),
            'projectsEntries' => Project::where('graduate_id', $graduate->id)->get(),
            'achievementEntries' => Achievement::where('graduate_id', $graduate->id)->get(),
            'testimonialsEntries' => Testimonial::where('graduate_id', $graduate->id)->get(),
            'employmentPreferences' => EmploymentPreference::where('graduate_id', $graduate->id)->first(),
            'careerGoals' => CareerGoal::where('graduate_id', $graduate->id)->first(),
            'resume' => Resume::where('graduate_id', $graduate->id)->first(),
            'internships' => $internships,
            'institutions' => $institutions,
            'educationLevels' => $educationLevels,
            'companies' => $companies,
            'locations' => $locations,
            'sectors' => $sectors,
        ]);
    }

    public function educationSettings()
    {
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        $educationEntries = GraduateEducation::where('graduate_id', $graduate->id)
            ->whereNull('deleted_at')
            ->get();

        $archivedEducationEntries = GraduateEducation::where('graduate_id', $graduate->id)
            ->whereNotNull('deleted_at')
            ->get();

        // Load institutions with degrees and programs
        $institutions = Institution::with([
            'institutionDegrees.degree:id,type,code',
            'institutionPrograms.program:id,name,degree_id' // code optional; remove if not present
        ])->get(['id', 'institution_name']);

        // Shape data for the front-end
        $institutions = $institutions->map(function ($inst) {
            return [
                'id' => $inst->id,
                'name' => $inst->institution_name,
                'degrees' => $inst->institutionDegrees
                    ->filter(fn($idg) => $idg->degree)
                    ->map(fn($idg) => [
                        'id' => $idg->degree->id,
                        'type' => $idg->degree->type,
                        'code' => $idg->degree->code ?? null,
                    ])
                    ->unique('id')
                    ->values(),
                'programs' => $inst->institutionPrograms
                    ->filter(fn($ip) => $ip->program)
                    ->map(fn($ip) => [
                        'id' => $ip->program->id,
                        'name' => $ip->program->name,
                        'degree_id' => $ip->program->degree_id,
                    ])
                    ->values(),
            ];
        })->values();

        return Inertia::render('Frontend/ProfileSettings/Education', [
            'user' => $user,
            'educationEntries' => $educationEntries,
            'archivedEducationEntries' => $archivedEducationEntries,
            'institutions' => $institutions, // <- pass structured list
        ]);
    }

    public function experienceSettings()
    {
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        // Fetch all experience entries for this graduate
        $experienceEntries = Experience::with('company')
        ->where('graduate_id', $graduate->id)
        ->whereNull('deleted_at')
        ->get();

        $archivedExperienceEntries = Experience::with('company')
            ->where('graduate_id', $graduate->id)
            ->whereNotNull('deleted_at')
            ->get();

        // Suggestion source
        $companies = Company::select('id', 'company_name as name')
            ->orderBy('company_name')
            ->get();

        return Inertia::render('Frontend/ProfileSettings/Experience', [
            'user' => $user,
            'experienceEntries' => $experienceEntries,
            'archivedExperienceEntries' => $archivedExperienceEntries,
            'companies' => $companies, 
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
        ]);
    }

    // Update profile information
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:1',
            'last_name' => 'required|string|max:255',
            'current_job_title' => 'nullable|string|max:255',
            'employment_status' => 'required|in:Employed,Underemployed,Unemployed',
            'profession' => 'nullable|string|max:255', // <-- add this line
            'graduate_location' => 'nullable|string|max:255', // Accept from frontend
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'contact_number' => 'nullable|string|max:15',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
            'graduate_ethnicity' => 'nullable|string|max:255',
            'graduate_address' => 'nullable|string|max:255',
            'graduate_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'graduate_about_me' => 'nullable|string|max:1000',
            'linkedin_url' => 'nullable|string|max:255',
            'github_url' => 'nullable|string|max:255',
            'personal_website' => 'nullable|string|max:255',
            'other_social_links' => 'nullable|string|max:1000',
        ]);
        /** @var \App\Models\User $user */

        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        $user->update([
            'email' => $validated['email'],
        ]);

        $employmentStatus = $validated['employment_status'];
        $currentJobTitle = $employmentStatus === 'Unemployed' ? '' : $validated['current_job_title'];

        $graduate->update([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'current_job_title' => $currentJobTitle,
            'employment_status' => $employmentStatus,
            'profession' => $validated['profession'] ?? $graduate->profession,

            'location' => $validated['graduate_location'] ?? $graduate->location, // Map to DB column
            'contact_number' => $validated['contact_number'] ?? $graduate->contact_number,
            'dob' => $validated['dob'] ?? null,
            'gender' => array_key_exists('gender', $validated) ? $validated['gender'] : $graduate->gender,
            'ethnicity' => $validated['graduate_ethnicity'] ?? null,
            'address' => $validated['graduate_address'] ?? null,
            'about_me' => $validated['graduate_about_me'] ?? null,
            // Add other graduate fields if needed
            'linkedin_url' => $validated['linkedin_url'] ?? $graduate->linkedin_url,
            'github_url' => $validated['github_url'] ?? $graduate->github_url,
            'personal_website' => $validated['personal_website'] ?? $graduate->personal_website,
            'other_social_links' => $validated['other_social_links'] ?? $graduate->other_social_links,
        ]);

        // Handle profile picture upload
        if ($request->hasFile('graduate_picture') && $request->file('graduate_picture')->isValid()) {
            $file = $request->file('graduate_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('profile_pictures', $filename, 'public');
            $user->profile_picture = $path;
            $user->save();

            $graduate->graduate_picture = $path;
            $graduate->save();
        }


        return redirect()->back()->with('flash.banner', 'Profile updated successfully!');
    }


    // Add education
    public function addEducation(Request $request)
    {
        $request->validate([
        'education_id'        => 'nullable|exists:educations,id',
        'level_of_education'  => 'nullable|string|max:100',
        'program'             => 'nullable|string|max:255',
        'school_name'         => 'required|string|max:255',
        'start_date'          => 'required|date',
        'end_date'            => 'nullable|date|after_or_equal:start_date',
        'is_current'          => 'boolean',
        'description'         => 'nullable|string',
        'achievement'         => 'nullable|string',
        ]);

        $graduateId = auth()->user()->graduate->id;

        // If this record is current, unset others
        if ($request->boolean('is_current')) {
            GraduateEducation::where('graduate_id', $graduateId)->update(['is_current' => false]);
        }

        GraduateEducation::create([
            'graduate_id'        => $graduateId,
            'education_id'       => $request->education_id,
            'level_of_education' => $request->level_of_education,
            'program'            => $request->program,
            'school_name'        => $request->school_name,
            'start_date'         => Carbon::parse($request->start_date)->format('Y-m-d'),
            'end_date'           => $request->boolean('is_current')
                                    ? null
                                    : ($request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d') : null),
            'is_current'         => $request->boolean('is_current'),
            'description'        => $request->description,
            'achievement'        => $request->achievement,
        ]);
        return redirect()->back()->with('flash.banner', 'Education added successfully.');
    }

    // Update education
    public function updateEducation(Request $request, $id)
    {
        $request->validate([
            'education_id'        => 'nullable|exists:educations,id',
            'level_of_education'  => 'nullable|string|max:100',
            'program'             => 'nullable|string|max:255',
            'school_name'         => 'required|string|max:255',
            'start_date'          => 'required|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
            'is_current'          => 'boolean',
            'description'         => 'nullable|string',
            'achievement'         => 'nullable|string',
        ]);

        $edu = GraduateEducation::findOrFail($id);

        if ($request->boolean('is_current')) {
            GraduateEducation::where('graduate_id', $edu->graduate_id)
                ->where('id', '!=', $edu->id)
                ->update(['is_current' => false]);
        }

        $edu->update([
            'education_id'       => $request->education_id,
            'level_of_education' => $request->level_of_education,
            'program'            => $request->program,
            'school_name'        => $request->school_name,
            'start_date'         => \Carbon\Carbon::parse($request->start_date)->format('Y-m-d'),
            'end_date'           => $request->boolean('is_current')
                                    ? null
                                    : ($request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('Y-m-d') : null),
            'is_current'         => $request->boolean('is_current'),
            'description'        => $request->description,
            'achievement'        => $request->achievement,
        ]);

        return redirect()->back()->with('flash.banner', 'Education updated successfully.');
    }

    public function archiveEducation($id)
    {
        $edu = GraduateEducation::where('id', $id)->whereNull('deleted_at')->firstOrFail();
        $edu->delete(); // soft delete
        return back()->with('flash.banner', 'Education archived.');
    }

    public function unarchiveEducation($id)
    {
        $edu = GraduateEducation::withTrashed()->where('id', $id)->firstOrFail();
        if ($edu->trashed()) {
            $edu->restore();
        }
        return back()->with('flash.banner', 'Education restored.');
    }

    // Remove education
    public function deleteEducation($id)
    {
       $education = GraduateEducation::withTrashed()->findOrFail($id);
        $education->forceDelete();

        return redirect()->back()->with('flash.banner', 'Education removed successfully.');
    }

    // Add experience
    public function addExperience(Request $request)
    {
        $request->validate([
            'graduate_experience_title' => 'required|string|max:255',
            'graduate_experience_start_date' => 'required|string',
            'graduate_experience_end_date' => 'nullable|string',
            'graduate_experience_address' => 'nullable|string|max:255',
            'graduate_experience_description' => 'nullable|string',
            'graduate_experience_employment_type' => 'nullable|string|max:255',
            'is_current' => 'boolean',
            'graduate_experience_company' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

         // Convert ISO8601 to Y-m-d
        $startDate = $request->graduate_experience_start_date
            ? Carbon::parse($request->graduate_experience_start_date)->format('Y-m-d')
            : null;
        $endDate = $request->is_current
            ? null
            : ($request->graduate_experience_end_date
                ? Carbon::parse($request->graduate_experience_end_date)->format('Y-m-d')
                : null);

        $experience = new Experience();
        $experience->graduate_id = $graduate->id;
        $experience->title = $request->graduate_experience_title;
        $experience->start_date = $startDate;
        $experience->end_date = $endDate;
        $experience->address = $request->graduate_experience_address;
        $experience->description = $request->graduate_experience_description ?? 'No description provided';
        $experience->employment_type = $request->graduate_experience_employment_type;
        $experience->company_name = $request->graduate_experience_company;
        $experience->is_current = filter_var($request->is_current, FILTER_VALIDATE_BOOLEAN);
        $experience->save();

        // Update current job title in graduates table if this is the current experience
        if ($request->is_current && $request->graduate_experience_title) {
            \App\Models\Graduate::where('id', $experience->graduate_id)
                ->update(['current_job_title' => $request->graduate_experience_title]);
        }

        return redirect()->back()->with('flash.banner', 'Experience added successfully.');
    }

    // Update experience
    public function updateExperience(Request $request, $id)
    {
            $request->validate([
            'graduate_experience_title' => 'required|string|max:255',
            'graduate_experience_start_date' => 'required|string',
            'graduate_experience_end_date' => 'nullable|string',
            'graduate_experience_address' => 'nullable|string|max:255',
            'graduate_experience_description' => 'nullable|string',
            'graduate_experience_employment_type' => 'nullable|string|max:255',
            'is_current' => 'boolean',
            'graduate_experience_company' => 'required|string|max:255',
        ]);

        $experience = Experience::findOrFail($id);

        // Convert ISO8601 to Y-m-d
        $startDate = $request->graduate_experience_start_date
            ? Carbon::parse($request->graduate_experience_start_date)->format('Y-m-d')
            : null;
        $endDate = $request->is_current
            ? null
            : ($request->graduate_experience_end_date
                ? Carbon::parse($request->graduate_experience_end_date)->format('Y-m-d')
                : null);

        $experience->title = $request->graduate_experience_title;
        $experience->start_date = $startDate;
        $experience->end_date = $endDate;
        $experience->address = $request->graduate_experience_address;
        $experience->description = $request->graduate_experience_description ?? 'No description provided';
        $experience->employment_type = $request->graduate_experience_employment_type;
        $experience->company_name = $request->graduate_experience_company;
        $experience->is_current = filter_var($request->is_current, FILTER_VALIDATE_BOOLEAN);
        $experience->save();

        // Update current job title in graduates table if this is the current experience
        if ($request->is_current && $request->graduate_experience_title) {
            \App\Models\Graduate::where('id', $experience->graduate_id)
                ->update(['current_job_title' => $request->graduate_experience_title]);
        }

        return redirect()->back()->with('flash.banner', 'Experience updated successfully.');
    }

    public function archiveExperience($id)
    {
        $exp = Experience::where('id', $id)->whereNull('deleted_at')->firstOrFail();
        $exp->delete(); // soft delete
        return back();
    }

    public function unarchiveExperience($id)
    {
        $exp = Experience::withTrashed()->findOrFail($id);
        if ($exp->trashed()) {
            $exp->restore();
        }
        return back();
    }

    // Remove experience
    public function removeExperience($id)
    {
        $experience = Experience::withTrashed()->findOrFail($id);
        $experience->forceDelete();

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
        \Log::info('Request all:', $request->all());

        $validated = $request->validate([
            'graduate_projects_title' => 'required|string|max:255',
            'graduate_projects_description' => 'nullable|string',
            'graduate_projects_role' => 'required|string|max:255',
            'graduate_projects_start_date' => 'required|date',
            'graduate_projects_end_date' => 'nullable|date',
            'graduate_projects_url' => 'nullable|string|max:255',
            'graduate_projects_key_accomplishments' => 'nullable|string',
            'graduate_project_file' => 'nullable|file|max:2048',
            'is_current' => 'boolean',

        ]);

        $project = Project::findOrFail($id);

        // Map frontend fields to DB columns
        $project->title = $validated['graduate_projects_title'];
        $project->description = $validated['graduate_projects_description'];
        $project->role = $validated['graduate_projects_role'];
        $project->start_date = $validated['graduate_projects_start_date'];
        $project->end_date = $validated['is_current'] ? null : $validated['graduate_projects_end_date'];
        $project->url = $validated['graduate_projects_url'];
        $project->key_accomplishments = $validated['graduate_projects_key_accomplishments'];

        // Handle file upload
        if ($request->hasFile('graduate_project_file')) {
            $filePath = $request->file('graduate_project_file')->store('project_files', 'public');
            $project->file = $filePath;
        }

        $project->save();

        // Return updated projects list
        return redirect()->back()->with('projectsEntries', Project::where('graduate_id', $project->graduate_id)->whereNull('deleted_at')->get());
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
            // 'content' => $request->content,
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
        // $testimonial->content = $request->content;

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
    function saveEmploymentReference(Request $request)
    {
        $request->validate([
            'job_types' => 'nullable|string',
            'salary_expectations' => 'nullable|string',
            'preferred_locations' => 'nullable|string',
            'work_environment' => 'nullable|string',
            'additional_notes' => 'nullable|string',
        ]);

        $user = Auth::upublicser();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        $employmentReference = EmploymentPreference::firstOrNew([
            'graduate_id' => $graduate->id
        ]);

        $employmentReference->job_types = $request->job_types;
        $employmentReference->salary_expectations = $request->salary_expectations;
        $employmentReference->preferred_locations = $request->preferred_locations;
        $employmentReference->work_environment = $request->work_environment;
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

    // Save employment preferences
    public function saveEmploymentPreferences(Request $request)
    {
        $request->validate([
            'job_types' => 'required|array',
            'preferred_locations' => 'required|array',
            'work_environment' => 'required|array',
            'employment_min_salary' => 'nullable|numeric',
            'employment_max_salary' => 'nullable|numeric',
            'salary_type' => 'required|string',
            'additional_notes' => 'nullable|string',
        ]);
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();
        $employmentPreference = $graduate->employmentPreference()->firstOrCreate([]);

        // 1. Job Types
        $jobTypeNames = [];
        $jobTypeIds = [];
        foreach ($request->job_types as $type) {
            $jobType = JobType::firstOrCreate(['type' => $type]);
            $jobTypeIds[] = $jobType->id;
            $jobTypeNames[] = $jobType->type;
        }
        $employmentPreference->jobTypes()->sync($jobTypeIds);
        $employmentPreference->job_type = implode(',', $jobTypeNames); // <-- Save names, not IDs

        // 2. Salary
        $salary = Salary::firstOrCreate([
            'employment_preference_id' => $employmentPreference->id,
            'salary_type' => $request->salary_type,
            'employment_min_salary' => $request->employment_min_salary,
            'employment_max_salary' => $request->employment_max_salary,
        ]);
        $employmentPreference->salary_id = $salary->id;
        $employmentPreference->employment_min_salary = $request->employment_min_salary;
        $employmentPreference->employment_max_salary = $request->employment_max_salary;
        $employmentPreference->salary_type = $request->salary_type;

        // 3. Locations
        $locationNames = [];
        $locationIds = [];
        foreach ($request->preferred_locations as $address) {
            $location = Location::firstOrCreate(['address' => $address]);
            $locationIds[] = $location->id;
            $locationNames[] = $location->address;
        }
        $employmentPreference->locations()->sync($locationIds);
        $employmentPreference->location = implode(',', $locationNames); // <-- Save names, not IDs

        // 4. Work Environments
        $workEnvIds = [];
        foreach ($request->work_environment as $env) {
            $workEnv = WorkEnvironment::firstOrCreate(['environment_type' => $env]);
            $workEnvIds[] = $workEnv->id;
        }
        $employmentPreference->workEnvironments()->sync($workEnvIds);
        $employmentPreference->work_environment = implode(',', $request->work_environment); // Save as CSV of names

        // 5. Additional Notes
        $employmentPreference->additional_notes = $request->additional_notes;
        $employmentPreference->save();

        return back()->with('flash.banner', 'Employment preferences saved successfully.');
    }

    // Alumni Stories Methods
    public function alumniStoriesSettings()
    {
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        // Fetch all alumni stories entries for this graduate
        $alumniStoriesEntries = \App\Models\AlumniStory::where('graduate_id', $graduate->id)
            ->whereNull('deleted_at')
            ->get();

        // Fetch archived alumni stories entries
        $archivedAlumniStoriesEntries = \App\Models\AlumniStory::where('graduate_id', $graduate->id)
            ->whereNotNull('deleted_at')
            ->get();

        return Inertia::render('Frontend/ProfileSettings/AlumniStories', [
            'user' => $user,
            'alumniStoriesEntries' => $alumniStoriesEntries,
            'archivedAlumniStoriesEntries' => $archivedAlumniStoriesEntries,
        ]);
    }

    public function addAlumniStory(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'video_url' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
        ]);

        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        $alumniStory = new \App\Models\AlumniStory();
        $alumniStory->graduate_id = $graduate->id;
        $alumniStory->title = $request->title;
        $alumniStory->content = $request->content;
        $alumniStory->video_url = $request->video_url;
        $alumniStory->is_featured = $request->is_featured ?? false;

        // Handle image upload
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('alumni_stories', $filename, 'public');
            $alumniStory->image = $path;
        }

        $alumniStory->save();

        return back()->with('success', 'Alumni story added successfully!');
    }

    public function updateAlumniStory(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'video_url' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
        ]);

        $alumniStory = \App\Models\AlumniStory::findOrFail($id);

        // Check if the story belongs to the authenticated user
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        if ($alumniStory->graduate_id !== $graduate->id) {
            return back()->with('error', 'Unauthorized action.');
        }

        $alumniStory->title = $request->title;
        $alumniStory->content = $request->content;
        $alumniStory->video_url = $request->video_url;
        $alumniStory->is_featured = $request->is_featured ?? false;

        // Handle image upload
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            // Delete old image if exists
            if ($alumniStory->image) {
                Storage::disk('public')->delete($alumniStory->image);
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('alumni_stories', $filename, 'public');
            $alumniStory->image = $path;
        }

        $alumniStory->save();

        return back()->with('success', 'Alumni story updated successfully!');
    }

    public function deleteAlumniStory($id)
    {
        $alumniStory = \App\Models\AlumniStory::findOrFail($id);

        // Check if the story belongs to the authenticated user
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        if ($alumniStory->graduate_id !== $graduate->id) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Soft delete the alumni story
        $alumniStory->delete();

        return back()->with('success', 'Alumni story deleted successfully!');
    }

    public function restoreAlumniStory($id)
    {
        $alumniStory = \App\Models\AlumniStory::withTrashed()->findOrFail($id);

        // Check if the story belongs to the authenticated user
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        if ($alumniStory->graduate_id !== $graduate->id) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Restore the alumni story
        $alumniStory->restore();

        return back()->with('success', 'Alumni story restored successfully!');
    }

    /**
     * Show the batch upload form
     */
    public function batchUploadForm()
    {
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();

        // Get degree codes for validation
        $degreeCodes = \App\Models\Degree::pluck('code')->toArray();

        // Get program codes for validation
        $programCodes = \App\Models\Program::pluck('code')->toArray();

        // Get company names for validation
        $companyNames = \App\Models\Company::pluck('company_name')->toArray();

        // Create a map of normalized company names to actual company names
        $companyNamesMap = \App\Models\Company::all()
            ->mapWithKeys(function ($company) {
                return [strtolower(preg_replace('/\s+/', ' ', trim($company->company_name))) => $company->company_name];
            })
            ->toArray();

        // Get institution school years for validation
        $institutionId = $graduate->institution_id;
        $institutionSchoolYears = \App\Models\InstitutionSchoolYear::where('institution_id', $institutionId)
            ->with('schoolYear')
            ->get()
            ->map(function ($item) {
                return [
                    'school_year_range' => $item->schoolYear->school_year_range,
                    'term' => $item->term,
                ];
            });

        return Inertia::render('Frontend/ProfileSettings/BatchUpload', [
            'degreeCodes' => $degreeCodes,
            'programCodes' => $programCodes,
            'companyNames' => $companyNames,
            'companyNamesMap' => $companyNamesMap,
            'institutionSchoolYears' => $institutionSchoolYears,
        ]);
    }

    /**
     * Handle batch upload of graduate profiles
     */
    public function batchUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $errors = [];
        $successCount = 0;
        $errorCount = 0;

        // Parse CSV file
        $handle = fopen($file, 'r');
        if (!$handle) {
            return response()->json(['status' => 'error', 'message' => 'Unable to read the CSV file.'], 400);
        }

        // Read header row
        $header = fgetcsv($handle);
        $requiredHeaders = ['EMAIL', 'FIRST_NAME', 'LAST_NAME', 'DEGREE_CODE', 'PROGRAM_CODE', 'YEAR_GRADUATED', 'TERM', 'DOB', 'GENDER', 'CONTACT_NUMBER', 'EMPLOYMENT_STATUS', 'COMPANY_NAME', 'CURRENT_JOB_TITLE'];

        // Validate headers
        $missingHeaders = array_diff($requiredHeaders, $header);
        if (!empty($missingHeaders)) {
            return response()->json([
                'status' => 'error',
                'message' => 'CSV file is missing required headers: ' . implode(', ', $missingHeaders)
            ], 400);
        }

        // Get current user's institution ID
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();
        $institutionId = $graduate->institution_id;

        // Get validation data
        $degreeCodes = \App\Models\Degree::pluck('code')->toArray();
        $programCodes = \App\Models\InstitutionProgram::pluck('program_code')->toArray();
        $companyNames = \App\Models\Company::pluck('company_name')->toArray();

        // Create a map of normalized company names to actual company names
        $companyNamesMap = \App\Models\Company::all()
            ->mapWithKeys(function ($company) {
                return [strtolower(preg_replace('/\s+/', ' ', trim($company->company_name))) => $company->company_name];
            })
            ->toArray();

        // Get institution school years for validation
        $institutionSchoolYears = \App\Models\InstitutionSchoolYear::where('institution_id', $institutionId)
            ->with('schoolYear')
            ->get()
            ->map(function ($item) {
                return [
                    'school_year_range' => $item->schoolYear->school_year_range,
                    'term' => $item->term,
                ];
            })
            ->toArray();

        // Process each row
        $rowNumber = 1; // Start from 1 to account for header row
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Map CSV columns to data array
            $data = array_combine($header, $row);
            $rowErrors = [];

            // Validate required fields
            $requiredFields = ['EMAIL', 'FIRST_NAME', 'LAST_NAME', 'DEGREE_CODE', 'PROGRAM_CODE', 'YEAR_GRADUATED', 'TERM', 'DOB', 'GENDER', 'CONTACT_NUMBER', 'EMPLOYMENT_STATUS'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $rowErrors[] = "$field is required";
                }
            }

            // Validate email format
            if (!empty($data['EMAIL']) && !filter_var($data['EMAIL'], FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = "Invalid email format";
            }

            // Validate if email already exists
            if (!empty($data['EMAIL']) && \App\Models\User::where('email', $data['EMAIL'])->exists()) {
                $rowErrors[] = "Email already exists";
            }

            // Validate DOB format
            if (!empty($data['DOB'])) {
                try {
                    $dob = \Carbon\Carbon::createFromFormat('Y-m-d', $data['DOB']);
                } catch (\Exception $e) {
                    $rowErrors[] = "Invalid date format for DOB. Use YYYY-MM-DD";
                }
            }

            // Validate degree code
            if (!empty($data['DEGREE_CODE']) && !in_array($data['DEGREE_CODE'], $degreeCodes)) {
                $rowErrors[] = "Invalid degree code";
            }

            // Validate program code
            if (!empty($data['PROGRAM_CODE']) && !in_array($data['PROGRAM_CODE'], $programCodes)) {
                $rowErrors[] = "Invalid program code";
            }

            // Validate year and term combination
            if (!empty($data['YEAR_GRADUATED']) && !empty($data['TERM'])) {
                $validYearTerm = false;
                foreach ($institutionSchoolYears as $schoolYear) {
                    if (
                        $schoolYear['school_year_range'] == $data['YEAR_GRADUATED'] &&
                        strtolower($schoolYear['term']) == strtolower($data['TERM'])
                    ) {
                        $validYearTerm = true;
                        break;
                    }
                }

                if (!$validYearTerm) {
                    $rowErrors[] = "Invalid year and term combination";
                }
            }

            // Validate gender
            if (!empty($data['GENDER']) && !in_array(strtolower($data['GENDER']), ['male', 'female', 'other'])) {
                $rowErrors[] = "Gender must be Male, Female, or Other";
            }

            // Validate employment status
            if (!empty($data['EMPLOYMENT_STATUS']) && !in_array(ucfirst(strtolower($data['EMPLOYMENT_STATUS'])), ['Employed', 'Underemployed', 'Unemployed'])) {
                $rowErrors[] = "Employment status must be Employed, Underemployed, or Unemployed";
            }

            // Validate company name based on employment status
            if (!empty($data['EMPLOYMENT_STATUS'])) {
                $employmentStatus = ucfirst(strtolower($data['EMPLOYMENT_STATUS']));

                if ($employmentStatus == 'Employed' || $employmentStatus == 'Underemployed') {
                    // For employed/underemployed, company name is required and must exist
                    if (empty($data['COMPANY_NAME'])) {
                        $rowErrors[] = "Company name is required for Employed/Underemployed graduates";
                    } else {
                        // Check if company exists (case-insensitive)
                        $normalizedCompanyName = strtolower(preg_replace('/\s+/', ' ', trim($data['COMPANY_NAME'])));
                        if (!array_key_exists($normalizedCompanyName, $companyNamesMap)) {
                            $rowErrors[] = "Company name does not exist in the system";
                        }
                    }
                } else if ($employmentStatus == 'Unemployed') {
                    // For unemployed, company name should be N/A
                    if ($data['COMPANY_NAME'] !== 'N/A') {
                        $rowErrors[] = "Company name should be 'N/A' for Unemployed graduates";
                    }
                }
            }

            // If there are errors, add them to the errors array and continue to the next row
            if (!empty($rowErrors)) {
                $errors[] = [
                    'row' => $rowNumber,
                    'errors' => $rowErrors
                ];
                $errorCount++;
                continue;
            }

            // Process valid row
            try {
                DB::beginTransaction();

                // Create user
                $user = new \App\Models\User();
                $user->name = $this->toTitleCase($data['FIRST_NAME']) . ' ' . $this->toTitleCase($data['LAST_NAME']);
                $user->email = $data['EMAIL'];
                $user->password = Hash::make($this->generatePassword());
                $user->role = 'graduate';
                $user->save();

                // Get degree and program IDs
                $degreeId = $this->getDegreeId($data['DEGREE_CODE']);
                $programId = $this->getProgramId($data['PROGRAM_CODE'], $degreeId);
                $institutionSchoolYearId = $this->getInstitutionSchoolYearId($data['YEAR_GRADUATED'], $data['TERM'], $institutionId);

                // Create graduate
                $graduate = new \App\Models\Graduate();
                $graduate->user_id = $user->id;
                $graduate->institution_id = $institutionId;
                $graduate->program_id = $programId;
                $graduate->institution_school_year_id = $institutionSchoolYearId;
                $graduate->first_name = $this->toTitleCase($data['FIRST_NAME']);
                $graduate->middle_name = !empty($data['MIDDLE_NAME']) ? $this->toTitleCase($data['MIDDLE_NAME']) : null;
                $graduate->last_name = $this->toTitleCase($data['LAST_NAME']);
                $graduate->dob = $data['DOB'];
                $graduate->gender = ucfirst(strtolower($data['GENDER']));
                $graduate->contact_number = $data['CONTACT_NUMBER'];
                $graduate->employment_status = ucfirst(strtolower($data['EMPLOYMENT_STATUS']));

                // Set company name and job title based on employment status
                if ($graduate->employment_status == 'Employed' || $graduate->employment_status == 'Underemployed') {
                    $normalizedCompanyName = strtolower(preg_replace('/\s+/', ' ', trim($data['COMPANY_NAME'])));
                    $graduate->company_name = $companyNamesMap[$normalizedCompanyName];
                    $graduate->current_job_title = !empty($data['CURRENT_JOB_TITLE']) ? $data['CURRENT_JOB_TITLE'] : null;
                } else {
                    $graduate->company_name = null;
                    $graduate->current_job_title = null;
                }

                $graduate->save();

                DB::commit();
                $successCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = [
                    'row' => $rowNumber,
                    'errors' => ["Database error: {$e->getMessage()}"]
                ];
                $errorCount++;
            }
        }

        fclose($handle);

        return response()->json([
            'status' => 'success',
            'message' => "Processed $successCount graduates successfully with $errorCount errors",
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors
        ]);
    }

    /**
     * Generate a random password
     */
    private function generatePassword()
    {
        return 'Password' . rand(100000, 999999);
    }

    /**
     * Convert a string to title case
     */
    private function toTitleCase($string)
    {
        return ucwords(strtolower($string));
    }

    /**
     * Get degree ID from code
     */
    private function getDegreeId($code)
    {
        $degree = \App\Models\Degree::where('code', $code)->first();

        if (!$degree) {
            // If degree doesn't exist, create a new one with the code
            $degree = new \App\Models\Degree();
            $degree->type = 'Bachelor'; // Default type
            $degree->code = $code;
            $degree->save();
        }

        return $degree->id;
    }

    /**
     * Get program ID from code and degree ID
     */
    private function getProgramId($code, $degreeId)
    {
        $institutionProgram = \App\Models\InstitutionProgram::where('program_code', $code)
            ->where('degree_id', $degreeId)
            ->first();
        return $institutionProgram ? $institutionProgram->program_id : null;
    }

    /**
     * Get institution school year ID from year, term, and institution ID
     */
    private function getInstitutionSchoolYearId($year, $term, $institutionId)
    {
        $schoolYear = \App\Models\SchoolYear::where('school_year_range', $year)->first();
        if (!$schoolYear) {
            return null;
        }

        $institutionSchoolYear = \App\Models\InstitutionSchoolYear::where('institution_id', $institutionId)
            ->where('school_year_id', $schoolYear->id)
            ->where('term', $term)
            ->first();

        return $institutionSchoolYear ? $institutionSchoolYear->id : null;
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $filePath = public_path('templates/profile_template.csv');
        return response()->download($filePath, 'profile_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Handle batch upload of graduate profiles from text format
     */
    public function batchUploadText(Request $request)
    {
        $request->validate([
            'text_data' => 'required|string',
        ]);

        $textData = $request->input('text_data');
        $errors = [];
        $successCount = 0;
        $errorCount = 0;

        // Get current user's institution ID
        $user = Auth::user();
        $graduate = \App\Models\Graduate::where('user_id', $user->id)->first();
        $institutionId = $graduate->institution_id;

        // Get validation data
        $degreeCodes = \App\Models\Degree::pluck('code')->toArray();
        $programCodes = \App\Models\Program::pluck('code')->toArray();
        $companyNames = \App\Models\Company::pluck('company_name')->toArray();

        // Create a map of normalized company names to actual company names
        $companyNamesMap = \App\Models\Company::all()
            ->mapWithKeys(function ($company) {
                return [strtolower(preg_replace('/\s+/', ' ', trim($company->company_name))) => $company->company_name];
            })
            ->toArray();

        // Get institution school years for validation
        $institutionSchoolYears = \App\Models\InstitutionSchoolYear::where('institution_id', $institutionId)
            ->with('schoolYear')
            ->get()
            ->map(function ($item) {
                return [
                    'school_year_range' => $item->schoolYear->school_year_range,
                    'term' => $item->term,
                ];
            })
            ->toArray();

        // Parse text data into structured format
        $profiles = $this->parseTextToProfiles($textData);

        // Process each profile
        foreach ($profiles as $index => $profile) {
            $rowNumber = $index + 1;
            $rowErrors = [];

            // Validate required fields
            $requiredFields = ['email', 'first_name', 'last_name', 'degree_code', 'program_code', 'year_graduated', 'term', 'dob', 'gender', 'contact_number', 'employment_status'];
            foreach ($requiredFields as $field) {
                if (empty($profile[$field])) {
                    $rowErrors[] = strtoupper($field) . " is required";
                }
            }

            // Validate email format
            if (!empty($profile['email']) && !filter_var($profile['email'], FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = "Invalid email format";
            }

            // Validate if email already exists
            if (!empty($profile['email']) && \App\Models\User::where('email', $profile['email'])->exists()) {
                $rowErrors[] = "Email already exists";
            }

            // Validate DOB format
            if (!empty($profile['dob'])) {
                try {
                    $dob = \Carbon\Carbon::createFromFormat('Y-m-d', $profile['dob']);
                } catch (\Exception $e) {
                    $rowErrors[] = "Invalid date format for DOB. Use YYYY-MM-DD";
                }
            }

            // Validate degree code
            if (!empty($profile['degree_code']) && !in_array($profile['degree_code'], $degreeCodes)) {
                $rowErrors[] = "Invalid degree code";
            }

            // Validate program code
            if (!empty($profile['program_code']) && !in_array($profile['program_code'], $programCodes)) {
                $rowErrors[] = "Invalid program code";
            }

            // Validate year and term combination
            if (!empty($profile['year_graduated']) && !empty($profile['term'])) {
                $validYearTerm = false;
                foreach ($institutionSchoolYears as $schoolYear) {
                    if (
                        $schoolYear['school_year_range'] == $profile['year_graduated'] &&
                        strtolower($schoolYear['term']) == strtolower($profile['term'])
                    ) {
                        $validYearTerm = true;
                        break;
                    }
                }

                if (!$validYearTerm) {
                    $rowErrors[] = "Invalid year and term combination";
                }
            }

            // Validate gender
            if (!empty($profile['gender']) && !in_array(strtolower($profile['gender']), ['male', 'female', 'other'])) {
                $rowErrors[] = "Gender must be Male, Female, or Other";
            }

            // Validate employment status
            if (!empty($profile['employment_status']) && !in_array(ucfirst(strtolower($profile['employment_status'])), ['Employed', 'Underemployed', 'Unemployed'])) {
                $rowErrors[] = "Employment status must be Employed, Underemployed, or Unemployed";
            }

            // Validate company name based on employment status
            if (!empty($profile['employment_status'])) {
                $employmentStatus = ucfirst(strtolower($profile['employment_status']));

                if ($employmentStatus == 'Employed' || $employmentStatus == 'Underemployed') {
                    // For employed/underemployed, company name is required and must exist
                    if (empty($profile['company_name'])) {
                        $rowErrors[] = "Company name is required for Employed/Underemployed graduates";
                    } else {
                        // Check if company exists (case-insensitive)
                        $normalizedCompanyName = strtolower(preg_replace('/\s+/', ' ', trim($profile['company_name'])));
                        if (!array_key_exists($normalizedCompanyName, $companyNamesMap)) {
                            $rowErrors[] = "Company name does not exist in the system";
                        }
                    }
                } else if ($employmentStatus == 'Unemployed') {
                    // For unemployed, company name should be N/A
                    if ($profile['company_name'] !== 'N/A') {
                        $rowErrors[] = "Company name should be 'N/A' for Unemployed graduates";
                    }
                }
            }

            // If there are errors, add them to the errors array and continue to the next row
            if (!empty($rowErrors)) {
                $errors[] = [
                    'row' => $rowNumber,
                    'errors' => $rowErrors
                ];
                $errorCount++;
                continue;
            }

            // Process valid row
            try {
                DB::beginTransaction();

                // Create user
                $user = new \App\Models\User();
                $user->name = $this->toTitleCase($profile['first_name']) . ' ' . $this->toTitleCase($profile['last_name']);
                $user->email = $profile['email'];
                $user->password = Hash::make($this->generatePassword());
                $user->role = 'graduate';
                $user->save();

                // Get degree and program IDs
                $degreeId = $this->getDegreeId($profile['degree_code']);
                $programId = $this->getProgramId($profile['program_code'], $degreeId);
                $institutionSchoolYearId = $this->getInstitutionSchoolYearId($profile['year_graduated'], $profile['term'], $institutionId);

                // Create graduate
                $graduate = new \App\Models\Graduate();
                $graduate->user_id = $user->id;
                $graduate->institution_id = $institutionId;
                $graduate->program_id = $programId;
                $graduate->institution_school_year_id = $institutionSchoolYearId;
                $graduate->first_name = $this->toTitleCase($profile['first_name']);
                $graduate->middle_name = !empty($profile['middle_name']) ? $this->toTitleCase($profile['middle_name']) : null;
                $graduate->last_name = $this->toTitleCase($profile['last_name']);
                $graduate->dob = $profile['dob'];
                $graduate->gender = ucfirst(strtolower($profile['gender']));
                $graduate->contact_number = $profile['contact_number'];
                $graduate->employment_status = ucfirst(strtolower($profile['employment_status']));

                // Set company name and job title based on employment status
                if ($graduate->employment_status == 'Employed' || $graduate->employment_status == 'Underemployed') {
                    $normalizedCompanyName = strtolower(preg_replace('/\s+/', ' ', trim($profile['company_name'])));
                    $graduate->company_name = $companyNamesMap[$normalizedCompanyName];
                    $graduate->current_job_title = !empty($profile['current_job_title']) ? $profile['current_job_title'] : null;
                } else {
                    $graduate->company_name = null;
                    $graduate->current_job_title = null;
                }

                $graduate->save();

                // Process additional profile data
                $this->processAdditionalProfileData($user->id, $profile);

                DB::commit();
                $successCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = [
                    'row' => $rowNumber,
                    'errors' => ["Database error: {$e->getMessage()}"]
                ];
                $errorCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Processed $successCount graduates successfully with $errorCount errors",
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors
        ]);
    }

    /**
     * Parse text data into structured profile data
     */
    private function parseTextToProfiles($textData)
    {
        $profiles = [];
        $currentProfile = [];

        // Split text into lines
        $lines = explode("\n", $textData);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if this is a new profile (starts with "Name:")
            if (preg_match('/^Name:\s+(.+)$/i', $line, $matches)) {
                // If we have a current profile, add it to the profiles array
                if (!empty($currentProfile)) {
                    $profiles[] = $this->normalizeProfileData($currentProfile);
                }

                // Start a new profile
                $currentProfile = [];

                // Parse the name
                $fullName = trim($matches[1]);
                $nameParts = explode(' ', $fullName);

                if (count($nameParts) >= 2) {
                    $currentProfile['first_name'] = $nameParts[0];
                    $currentProfile['last_name'] = end($nameParts);

                    // If there are more than 2 parts, assume middle name(s)
                    if (count($nameParts) > 2) {
                        $middleNames = array_slice($nameParts, 1, -1);
                        $currentProfile['middle_name'] = implode(' ', $middleNames);
                    }
                } else {
                    // Handle case with only one name
                    $currentProfile['first_name'] = $fullName;
                    $currentProfile['last_name'] = '';
                }

                continue;
            }

            // Parse other fields
            if (preg_match('/^([^:]+):\s*(.*)$/i', $line, $matches)) {
                $key = trim($matches[1]);
                $value = trim($matches[2]);

                switch (strtolower($key)) {
                    case 'short-term goals':
                        $currentProfile['short_term_goals'] = $value;
                        break;
                    case 'long-term goals':
                        $currentProfile['long_term_goals'] = $value;
                        break;
                    case 'industries of interest':
                        $currentProfile['industries_of_interest'] = $value;
                        break;
                    case 'career path':
                        $currentProfile['career_path'] = $value;
                        break;
                    case 'job types':
                        $currentProfile['job_types'] = $value;
                        break;
                    case 'salary expectations':
                        $currentProfile['salary_expectations'] = $value;
                        break;
                    case 'preferred locations':
                        $currentProfile['preferred_locations'] = $value;
                        break;
                    case 'work environment':
                        $currentProfile['work_environment'] = $value;
                        break;
                    case 'additional notes':
                        $currentProfile['additional_notes'] = $value;
                        break;
                    case 'project title':
                        $currentProfile['project_title'] = $value;
                        break;
                    case 'description':
                        $currentProfile['project_description'] = $value;
                        break;
                    case 'role':
                        $currentProfile['project_role'] = $value;
                        break;
                    case 'start date':
                        $currentProfile['project_start_date'] = $value;
                        break;
                    case 'end date':
                        $currentProfile['project_end_date'] = $value;
                        break;
                    case 'key accomplishments':
                        $currentProfile['project_accomplishments'] = $value;
                        break;
                    case 'certification name':
                        $currentProfile['certification_name'] = $value;
                        break;
                    case 'issuer':
                        $currentProfile['certification_issuer'] = $value;
                        break;
                    case 'issue date':
                        $currentProfile['certification_issue_date'] = $value;
                        break;
                    case 'expiry date':
                        $currentProfile['certification_expiry_date'] = $value;
                        break;
                    case 'credential url':
                        $currentProfile['certification_url'] = $value;
                        break;
                    case 'credential id':
                        $currentProfile['certification_id'] = $value;
                        break;
                    case 'achievement title':
                        $currentProfile['achievement_title'] = $value;
                        break;
                    case 'achievement type':
                        $currentProfile['achievement_type'] = $value;
                        break;
                    case 'date':
                        $currentProfile['achievement_date'] = $value;
                        break;
                    case 'url':
                        $currentProfile['achievement_url'] = $value;
                        break;
                    case 'testimonial from':
                        $currentProfile['testimonial_from'] = $value;
                        break;
                    case 'role/title':
                        $currentProfile['testimonial_role'] = $value;
                        break;
                    case 'testimonial':
                        $currentProfile['testimonial_content'] = $value;
                        break;
                    case 'skill name 1':
                    case 'skill name 2':
                    case 'skill name 3':
                        $skillIndex = substr($key, -1);
                        $currentProfile['skill_' . $skillIndex . '_name'] = $value;
                        break;
                    case 'proficiency level':
                        // Find which skill this belongs to
                        if (!isset($currentProfile['skill_1_proficiency']) && isset($currentProfile['skill_1_name'])) {
                            $currentProfile['skill_1_proficiency'] = $value;
                        } else if (!isset($currentProfile['skill_2_proficiency']) && isset($currentProfile['skill_2_name'])) {
                            $currentProfile['skill_2_proficiency'] = $value;
                        } else if (!isset($currentProfile['skill_3_proficiency']) && isset($currentProfile['skill_3_name'])) {
                            $currentProfile['skill_3_proficiency'] = $value;
                        }
                        break;
                    case 'skill type':
                        // Find which skill this belongs to
                        if (!isset($currentProfile['skill_1_type']) && isset($currentProfile['skill_1_name'])) {
                            $currentProfile['skill_1_type'] = $value;
                        } else if (!isset($currentProfile['skill_2_type']) && isset($currentProfile['skill_2_name'])) {
                            $currentProfile['skill_2_type'] = $value;
                        } else if (!isset($currentProfile['skill_3_type']) && isset($currentProfile['skill_3_name'])) {
                            $currentProfile['skill_3_type'] = $value;
                        }
                        break;
                    case 'years of experience':
                        // Find which skill this belongs to
                        if (!isset($currentProfile['skill_1_years']) && isset($currentProfile['skill_1_name'])) {
                            $currentProfile['skill_1_years'] = $value;
                        } else if (!isset($currentProfile['skill_2_years']) && isset($currentProfile['skill_2_name'])) {
                            $currentProfile['skill_2_years'] = $value;
                        } else if (!isset($currentProfile['skill_3_years']) && isset($currentProfile['skill_3_name'])) {
                            $currentProfile['skill_3_years'] = $value;
                        }
                        break;
                    case 'employment type':
                        $currentProfile['employment_status'] = $value;
                        break;
                    case 'experience title':
                        $currentProfile['experience_title'] = $value;
                        break;
                    case 'experience company':
                        $currentProfile['experience_company'] = $value;
                        break;
                    case 'experience start date':
                        $currentProfile['experience_start_date'] = $value;
                        break;
                    case 'experience end date':
                        $currentProfile['experience_end_date'] = $value;
                        break;
                    case 'experience address':
                        $currentProfile['experience_address'] = $value;
                        break;
                    case 'experience description':
                        $currentProfile['experience_description'] = $value;
                        break;
                }
            }
        }

        // Add the last profile if it exists
        if (!empty($currentProfile)) {
            $profiles[] = $this->normalizeProfileData($currentProfile);
        }

        return $profiles;
    }

    /**
     * Normalize profile data to match required fields
     */
    private function normalizeProfileData($profile)
    {
        // Generate an email if not provided
        if (!isset($profile['email'])) {
            $firstName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $profile['first_name']));
            $lastName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $profile['last_name']));
            $profile['email'] = $firstName . '.' . $lastName . '@example.com';
        }

        // Set default values for required fields
        $defaults = [
            'degree_code' => 'BS-001',  // Default degree code
            'program_code' => 'BSCS-001',  // Default program code
            'year_graduated' => '2022-2023',  // Default year
            'term' => '1',  // Default term
            'dob' => '2000-01-01',  // Default DOB
            'gender' => 'Male',  // Default gender
            'contact_number' => '9123456789',  // Default contact number
            'company_name' => 'N/A',  // Default company name
            'current_job_title' => 'N/A',  // Default job title
        ];

        // Apply defaults for missing fields
        foreach ($defaults as $key => $value) {
            if (!isset($profile[$key]) || empty($profile[$key])) {
                $profile[$key] = $value;
            }
        }

        // Map employment type to employment status
        if (isset($profile['employment_status'])) {
            $status = strtolower($profile['employment_status']);
            if (strpos($status, 'full') !== false || strpos($status, 'part') !== false || strpos($status, 'contract') !== false) {
                $profile['employment_status'] = 'Employed';
            } else if (strpos($status, 'unemployed') !== false) {
                $profile['employment_status'] = 'Unemployed';
            } else if (strpos($status, 'underemployed') !== false) {
                $profile['employment_status'] = 'Underemployed';
            } else {
                $profile['employment_status'] = 'Employed';  // Default to employed
            }
        }

        return $profile;
    }

    /**
     * Process additional profile data (skills, projects, certifications, etc.)
     */
    private function processAdditionalProfileData($userId, $profile)
    {
        $graduate = \App\Models\Graduate::where('user_id', $userId)->first();

        // Process career goals
        if (!empty($profile['short_term_goals']) || !empty($profile['long_term_goals'])) {
            $careerGoal = new \App\Models\CareerGoal();
            $careerGoal->graduate_id = $graduate->id;
            $careerGoal->short_term_goals = $profile['short_term_goals'] ?? null;
            $careerGoal->long_term_goals = $profile['long_term_goals'] ?? null;
            $careerGoal->industries_of_interest = $profile['industries_of_interest'] ?? null;
            $careerGoal->career_path = $profile['career_path'] ?? null;
            $careerGoal->save();
        }

        // Process employment preferences
        if (!empty($profile['job_types']) || !empty($profile['salary_expectations']) || !empty($profile['preferred_locations']) || !empty($profile['work_environment'])) {
            $employmentPreference = new \App\Models\EmploymentPreference();
            $employmentPreference->graduate_id = $graduate->id;
            $employmentPreference->job_types = $profile['job_types'] ?? null;
            $employmentPreference->salary_expectations = $profile['salary_expectations'] ?? null;
            $employmentPreference->preferred_locations = $profile['preferred_locations'] ?? null;
            $employmentPreference->work_environment = $profile['work_environment'] ?? null;
            $employmentPreference->save();
        }

        // Process project
        if (!empty($profile['project_title'])) {
            $project = new \App\Models\Project();
            $project->graduate_id = $graduate->id;
            $project->title = $profile['project_title'];
            $project->description = $profile['project_description'] ?? null;
            $project->role = $profile['project_role'] ?? null;

            // Parse dates
            if (!empty($profile['project_start_date'])) {
                try {
                    $project->start_date = \Carbon\Carbon::parse($profile['project_start_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    // Use current date if parsing fails
                    $project->start_date = now()->format('Y-m-d');
                }
            }

            if (!empty($profile['project_end_date'])) {
                try {
                    $project->end_date = \Carbon\Carbon::parse($profile['project_end_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    // Use current date if parsing fails
                    $project->end_date = now()->format('Y-m-d');
                }
            }

            $project->key_accomplishments = $profile['project_accomplishments'] ?? null;
            $project->save();
        }

        // Process experience
        if (!empty($profile['experience_title'])) {
            $experience = new \App\Models\Experience();
            $experience->graduate_id = $graduate->id;
            $experience->title = $profile['experience_title'];
            $experience->company_name = $profile['experience_company'] ?? null;

            // Parse start date
            if (!empty($profile['experience_start_date'])) {
                try {
                    $experience->start_date = \Carbon\Carbon::parse($profile['experience_start_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    // Use current date if parsing fails
                    $experience->start_date = now()->format('Y-m-d');
                }
            }

            // Parse end date or set is_current if "Present"
            if (!empty($profile['experience_end_date'])) {
                if (strtolower($profile['experience_end_date']) === 'present') {
                    $experience->is_current = true;
                    $experience->end_date = null;
                } else {
                    try {
                        $experience->end_date = \Carbon\Carbon::parse($profile['experience_end_date'])->format('Y-m-d');
                        $experience->is_current = false;
                    } catch (\Exception $e) {
                        // Use current date if parsing fails
                        $experience->end_date = now()->format('Y-m-d');
                        $experience->is_current = false;
                    }
                }
            }

            $experience->address = $profile['experience_address'] ?? null;
            $experience->description = $profile['experience_description'] ?? null;
            $experience->employment_type = $profile['employment_type'] ?? 'Full-time';
            $experience->save();
        }

        // Process certification
        if (!empty($profile['certification_name'])) {
            $certification = new \App\Models\Certification();
            $certification->graduate_id = $graduate->id;
            $certification->name = $profile['certification_name'];
            $certification->issuer = $profile['certification_issuer'] ?? null;

            // Parse dates
            if (!empty($profile['certification_issue_date'])) {
                try {
                    $certification->issue_date = \Carbon\Carbon::parse($profile['certification_issue_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    // Use current date if parsing fails
                    $certification->issue_date = now()->format('Y-m-d');
                }
            }

            if (!empty($profile['certification_expiry_date'])) {
                try {
                    $certification->expiry_date = \Carbon\Carbon::parse($profile['certification_expiry_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    // Use current date if parsing fails
                    $certification->expiry_date = now()->format('Y-m-d');
                }
            }

            $certification->credential_url = $profile['certification_url'] ?? null;
            $certification->credential_id = $profile['certification_id'] ?? null;
            $certification->save();
        }

        // Process achievement
        if (!empty($profile['achievement_title'])) {
            $achievement = new \App\Models\Achievement();
            $achievement->graduate_id = $graduate->id;
            $achievement->title = $profile['achievement_title'];
            $achievement->type = $profile['achievement_type'] ?? 'Academic';

            // Parse date
            if (!empty($profile['achievement_date'])) {
                try {
                    $achievement->date = \Carbon\Carbon::parse($profile['achievement_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    // Use current date if parsing fails
                    $achievement->date = now()->format('Y-m-d');
                }
            }

            $achievement->url = $profile['achievement_url'] ?? null;
            $achievement->save();
        }

        // Process testimonial
        if (!empty($profile['testimonial_from'])) {
            $testimonial = new \App\Models\Testimonial();
            $testimonial->graduate_id = $graduate->id;
            $testimonial->from = $profile['testimonial_from'];
            $testimonial->role = $profile['testimonial_role'] ?? null;
            $testimonial->testimonial = $profile['testimonial_content'] ?? null;
            $testimonial->save();
        }

        // Process skills
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($profile['skill_' . $i . '_name'])) {
                $skill = new \App\Models\GraduateSkill();
                $skill->graduate_id = $graduate->id;
                $skill->name = $profile['skill_' . $i . '_name'];
                $skill->proficiency_level = $profile['skill_' . $i . '_proficiency'] ?? 'Intermediate';
                $skill->skill_type = $profile['skill_' . $i . '_type'] ?? 'Technical Skill';
                $skill->years_of_experience = $profile['skill_' . $i . '_years'] ?? 1;
                $skill->save();
            }
        }
    }
}
