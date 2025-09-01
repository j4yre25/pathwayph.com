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
use App\Models\Degree;
use App\Models\Program;
use App\Models\Institution;
use App\Models\SchoolYear;
use App\Models\InstitutionDegree;
use App\Models\InstitutionProgram;
use App\Models\Company;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GraduateProfileController extends Controller
{
    public function show($id)
    {
        $graduate = Graduate::with([
            'user',
            'institution',
            'program',
            'institutionSchoolYear.schoolYear'
        ])->findOrFail($id);

        // Get year graduated and term
        $yearGraduated = null;
        $term = null;
        if ($graduate->institutionSchoolYear) {
            $yearGraduated = optional($graduate->institutionSchoolYear->schoolYear)->school_year_range;
            $term = $graduate->institutionSchoolYear->term;
        }

        // Get the authenticated user
        $user = Auth::user();

        return inertia('Frontend/UpdatedGraduateProfile', [
            'graduate' => $graduate,
            'originalInstitution' => [
                'name' => optional($graduate->institution)->institution_name,
                'program' => optional($graduate->program)->name,
                'year_graduated' => $yearGraduated,
                'term' => $term,
            ],
            'skills' => GraduateSkill::where('graduate_id', $graduate->id)
                ->join('skills', 'graduate_skills.skill_id', '=', 'skills.id')
                ->select('graduate_skills.*', 'skills.name as skill_name')
                ->get(),
            'experiences' => Experience::where('graduate_id', $graduate->id)->get(),
            'education' => Education::with('institution')
                ->where('graduate_id', $graduate->id)
                ->get(),
            'projects' => Project::where('graduate_id', $graduate->id)->get(),
            'achievements' => Achievement::where('graduate_id', $graduate->id)->get(),
            'certifications' => Certification::where('graduate_id', $graduate->id)->get(),
            'testimonials' => Testimonial::where('graduate_id', $graduate->id)->get(),
            'employmentPreferences' => \App\Models\EmploymentPreference::where('graduate_id', $graduate->id)->first(),
            'careerGoals' => \App\Models\CareerGoal::where('graduate_id', $graduate->id)->first(),
            'resume' => Resume::where('graduate_id', $graduate->id)->first(),
            'auth' => [
                'user' => $user,
            ],
            'roles' => $user ? $user->role : null,
        ]);
    }

    public function showInformationForm()
    {
        $user = auth()->user();
        $schools = Institution::all();
        $programs = Program::all();
        $degrees = Degree::all();
        $schoolYears = SchoolYear::all();
        $institutionDegrees = InstitutionDegree::all();
        $institutionPrograms = InstitutionProgram::all();
        $companies = Company::all();

        return Inertia::render('Frontend/InformationSection', [
            'email' => $user->email,
            'institutions' => $schools,
            'programs' => $programs,
            'degrees' => $degrees,
            'schoolYears' => $schoolYears,
            'institutionDegrees' => $institutionDegrees,
            'institutionPrograms' => $institutionPrograms,
            'companies' => $companies,
        ]);
    }

    public function saveInformation(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'mobile_number' => 'required|string|max:20',
            'graduate_school_graduated_from' => 'required|exists:institutions,id',
            'graduate_program_completed' => 'required|exists:programs,id',
            'graduate_year_graduated' => 'required|exists:school_years,id',
            'graduate_degree' => 'required|exists:degrees,id',
            'company_not_found' => 'nullable|boolean',
            'company_name' => 'nullable|string|max:255',
            'other_company_name' => 'nullable|string|max:255',
            'other_company_sector' => 'nullable|string|max:255',
            'current_job_title' => 'nullable|string|max:255',
            'employment_status' => 'nullable|string|max:255',
        ]);
        \Log::info('Validated:', $validated);
        // Handle company_id if company is selected from the list
        $companyId = null;
        if (empty($validated['company_not_found']) && !empty($validated['company_name'])) {
            $company = Company::where('company_name', $validated['company_name'])->first();
            if ($company) {
                $companyId = $company->id;
            }
        }

        $graduate = Graduate::create([
            'user_id' => $user->id,
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'dob' => $validated['dob'],
            'gender' => $validated['gender'],
            'contact_number' => $validated['mobile_number'],
            'institution_id' => $validated['graduate_school_graduated_from'],
            'program_id' => $validated['graduate_program_completed'],
            'school_year_id' => $validated['graduate_year_graduated'],
            'degree_id' => $validated['graduate_degree'],
            'company_id' => $companyId,
            'other_company_name' => $validated['other_company_name'] ?? null,
            'other_company_sector' => $validated['other_company_sector'] ?? null,
            'current_job_title' => $validated['current_job_title'] ?? '',
            'employment_status' => $validated['employment_status'] ?? '',
        ]);

        $user->is_approved = null;
        $user->has_completed_information = true;
        $user->save();

        session(['information_completed' => true]);
        return redirect()->route('graduates.profile', ['id' => $graduate->id])->with('information_completed', true);
    }
}
