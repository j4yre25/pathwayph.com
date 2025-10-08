<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Achievement;
use App\Models\Certification;
use App\Models\GraduateEducation;
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
use App\Models\Sector;
use App\Notifications\GraduateNeedsApprovalNotification;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GraduateProfileController extends Controller
{
    public function show($id)
    {
        $graduate = Graduate::with([
            'user',
            'education',
            'institution',
            'program',
            'institutionSchoolYear.schoolYear',
        ])->findOrFail($id);

        $sectors = Sector::all(); // Get all sectors


        // Get year graduated and term
        $yearGraduated = null;
        $term = null;
        if ($graduate->institutionSchoolYear && $graduate->institutionSchoolYear->schoolYear) {
            $yearGraduated = $graduate->institutionSchoolYear->schoolYear->school_year_range;
        }

        // Get the authenticated user
        $user = Auth::user();
        $graduateArray = $graduate->toArray();
        $graduateArray['year_graduated'] = $yearGraduated;

        $educationQuery = GraduateEducation::with([
            'education:id,name',
            'institution:institution_name'
        ])->where('graduate_id', $graduate->id);

        $educationCollection = $educationQuery->get();

        // Compute highest education (rank + recency)
        $rankMap = [
            'phd' => 1,
            'doctor' => 1,
            'doctorate' => 1,
            "master's" => 2,
            'masters' => 2,
            'master' => 2,
            "bachelor's" => 3,
            'bachelors' => 3,
            'bachelor' => 3,
            'associate' => 4,
            'certificate' => 5,
            'vocational' => 6,
            'senior high' => 7,
            'high school' => 7,
        ];

        $normalize = fn($v) => strtolower(trim($v ?? ''));

        $highestEducationModel = $educationCollection
            ->map(function ($e) {
                return [
                    'id' => $e->id,
                    'school_name' => $e->school_name
                        ?: optional($e->institution)->institution_name
                        ?: null,
                    'program' => $e->program,
                    'level_of_education' => $e->level_of_education
                        ?: optional($e->education)->name,
                    'start_date' => $e->start_date,
                    'end_date' => $e->is_current ? null : $e->end_date,
                    'is_current' => (bool) $e->is_current,
                    'description' => $e->description,
                    'achievement' => $e->achievement,
                ];
            })
            ->sort(function ($a, $b) use ($rankMap, $normalize) {
                $ra = $rankMap[$normalize($a['level_of_education'])] ?? 999;
                $rb = $rankMap[$normalize($b['level_of_education'])] ?? 999;
                if ($ra !== $rb)
                    return $ra <=> $rb;

                // Current first
                if ($a['is_current'] && !$b['is_current'])
                    return -1;
                if (!$a['is_current'] && $b['is_current'])
                    return 1;

                // Later end_date (or start_date) wins
                $aEnd = $a['end_date'] ?? $a['start_date'];
                $bEnd = $b['end_date'] ?? $b['start_date'];
                return strcmp((string) $bEnd, (string) $aEnd);
            })
            ->first();

        $educationTransformed = $educationCollection->map(function ($e) {
            return [
                'id' => $e->id,
                'school_name' => $e->school_name
                    ?: optional($e->institution)->institution_name
                    ?: null,
                'program' => $e->program,
                'level_of_education' => $e->level_of_education
                    ?: optional($e->education)->name,
                'start_date' => $e->start_date,
                'end_date' => $e->is_current ? null : $e->end_date,
                'is_current' => (bool) $e->is_current,
                'description' => $e->description,
                'achievement' => $e->achievement,
                'deleted_at' => $e->deleted_at,
            ];
        });

        return inertia('Frontend/UpdatedGraduateProfile', [
            'graduate' => $graduateArray,
            'originalInstitution' => [
                'name' => optional($graduate->institution)->institution_name,
                'program' => optional($graduate->program)->name,
                'year_graduated' => $yearGraduated,
                'term' => $term,
            ],
            'sectors' => $sectors,
            'skills' => GraduateSkill::where('graduate_id', $graduate->id)
                ->join('skills', 'graduate_skills.skill_id', '=', 'skills.id')
                ->select('graduate_skills.*', 'skills.name as skill_name')
                ->get(),
            'experiences' => Experience::where('graduate_id', $graduate->id)->get(),
            'education' => $educationTransformed,
            'highestEducation' => $highestEducationModel,
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
        $schoolYears = \App\Models\InstitutionSchoolYear::with('schoolYear')->get();
        $institutionDegrees = InstitutionDegree::all();
        $institutionPrograms = InstitutionProgram::all();
        $companies = Company::all();
        $sectors = Sector::all();


        return Inertia::render('Frontend/InformationSection', [
            'email' => $user->email,
            'institutions' => $schools,
            'programs' => $programs,
            'degrees' => $degrees,
            'schoolYears' => $schoolYears,
            'institutionDegrees' => $institutionDegrees,
            'institutionPrograms' => $institutionPrograms,
            'companies' => $companies,
            'sectors' => $sectors,
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
            'graduate_year_graduated' => 'required|string|regex:/^\d{4}-\d{4}$/',
            'graduate_degree' => 'required|exists:degrees,id',
            'company_not_found' => 'nullable|boolean',
            'company_name' => 'nullable|string|max:255',
            'other_company_name' => 'nullable|string|max:255',
            'other_company_sector' => 'nullable|string|max:255',
            'current_job_title' => 'nullable|string|max:255',
            'employment_status' => 'nullable|string|max:255',
            'graduate_term' => ['required', 'string', 'in:1,2,3,4,5,summer'],
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


        $sector = null;
        if ($request->company_not_found) {
            $sectorId = $request->input('other_company_sector');
            $sector = \App\Models\Sector::find($sectorId);
        }

        // Get the school year range and term from the form
        $schoolYearRange = $request->input('graduate_year_graduated');
        $term = strtolower(trim($request->input('graduate_term')));

        // Find or create the school year
        $schoolYear = SchoolYear::where('school_year_range', $schoolYearRange)->first();
        if (!$schoolYear) {
            $schoolYear = SchoolYear::create(['school_year_range' => $schoolYearRange]);
        }

        // Find or create the institution school year + term
        $institutionSchoolYear = \App\Models\InstitutionSchoolYear::where('institution_id', $validated['graduate_school_graduated_from'])
            ->where('school_year_range_id', $schoolYear->id)
            ->where('term', $term)
            ->first();

        if (!$institutionSchoolYear) {
            $institutionSchoolYear = \App\Models\InstitutionSchoolYear::create([
                'school_year_range_id' => $schoolYear->id,
                'term' => $term,
                'institution_id' => $validated['graduate_school_graduated_from'],
            ]);
        }

        // Use the ID for saving
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
            'school_year_id' => $schoolYear->id,
            'institution_school_year_id' => $institutionSchoolYear->id, // If you have this field
            'degree_id' => $validated['graduate_degree'],
            'company_id' => $companyId,
            'other_company_name' => $validated['other_company_name'] ?? null,
            'other_company_sector' => $sector ? $sector->name : null,
            'current_job_title' => $validated['current_job_title'] ?? '',
            'employment_status' => $validated['employment_status'] ?? '',
        ]);

        if (
            in_array($graduate->employment_status, ['Employed', 'Underemployed']) &&
            !empty($validated['current_job_title'])
        ) {
            \App\Models\Experience::create([
                'graduate_id' => $graduate->id,
                'title' => $validated['current_job_title'],
                'company_name' => $companyId
                    ? optional(\App\Models\Company::find($companyId))->company_name
                    : ($validated['other_company_name'] ?? $validated['company_name'] ?? null),
                'start_date' => now()->format('Y-m-d'),
                'is_current' => true,
                'employment_type' => '',
                'address' => null,
                'description' => null,
            ]);
        }



        $user->is_approved = null;
        $user->has_completed_information = true;
        $user->save();
        $institution = Institution::find($graduate->institution_id);
        $instiUser = $institution ? $institution->user : null;

        if ($instiUser) {
            $instiUser->notify(new GraduateNeedsApprovalNotification($graduate));
        }

        session(['information_completed' => true]);
        return redirect()->route('graduates.profile', ['id' => $graduate->id])->with('information_completed', true);
    }

    public function add(Request $request)
    {
        $request->validate([
            'institution_id' => 'required|exists:institutions,id',
            'school_year_range' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'graduate_user_id' => 'required|exists:users,id', // Pass the graduate's user ID
            'term' => ['required', 'string', 'in:1,2,3,4,5,summer'],
        ]);

        $user = \App\Models\User::find($request->graduate_user_id);

        // Only proceed if the user is approved
        if ($user && $user->is_approved) {
            [$start, $end] = explode('-', $request->school_year_range);
            if ((int) $start >= (int) $end) {
                return response()->json(['message' => 'Invalid range (start year must be less than end year).'], 422);
            }

            // Check if school year exists globally
            $schoolYear = \App\Models\SchoolYear::withTrashed()
                ->where('school_year_range', $request->school_year_range)
                ->first();

            if (!$schoolYear) {
                $schoolYear = \App\Models\SchoolYear::create([
                    'school_year_range' => $request->school_year_range,
                ]);
            }

            // Check if this institution already has this school year and term
            $exists = \App\Models\InstitutionSchoolYear::withTrashed()
                ->where('institution_id', $request->institution_id)
                ->where('school_year_range_id', $schoolYear->id)
                ->where('term', $request->term)
                ->exists();

            if (!$exists) {
                \App\Models\InstitutionSchoolYear::create([
                    'school_year_range_id' => $schoolYear->id,
                    'term' => $request->term,
                    'institution_id' => $request->institution_id,
                ]);
            }
        }


    }
}
