<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewGraduate;
use App\Models\Graduate;
use App\Models\Program;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Carbon\Carbon;

class GraduateController extends Controller
{
    public function index(Request $request)
    {
        $institutionId = Auth::user()->institution->id;

        $query = DB::table('graduates')
            ->join('users', 'graduates.user_id', '=', 'users.id')
            ->join('programs', 'graduates.program_id', '=', 'programs.id')
            ->join('institution_school_years', 'graduates.school_year_id', '=', 'institution_school_years.id')
            ->join('school_years', 'institution_school_years.school_year_range_id', '=', 'school_years.id')
            ->where('users.role', 'graduate')
            ->where('graduates.institution_id', $institutionId)
            ->where('users.is_approved', true)
            ->whereNull('graduates.deleted_at')
            ->select(
                'graduates.id as graduate_id',
                'graduates.user_id',
                'graduates.first_name',
                'graduates.middle_name',
                'graduates.last_name',
                'graduates.dob',
                'graduates.gender',
                'graduates.contact_number',
                'graduates.program_id',
                'graduates.school_year_id',
                'programs.name as program_name',
                'school_years.school_year_range as year_graduated',
                'institution_school_years.term as term',
                'graduates.current_job_title',
                'graduates.employment_status',
                'users.email'
            );

        // Apply filters from request
        if ($request->filled('name') && $request->input('name') !== 'all') {
            $name = $request->input('name');
            $query->where(function($q) use ($name) {
                $q->where('graduates.first_name', 'like', "%$name%")
                  ->orWhere('graduates.middle_name', 'like', "%$name%")
                  ->orWhere('graduates.last_name', 'like', "%$name%");
            });
        }
        if ($request->filled('year') && $request->input('year') !== 'all') {
            $query->where('school_years.school_year_range', $request->input('year'));
        }
        if ($request->filled('term') && $request->input('term') !== 'all') {
            $query->where('institution_school_years.term', $request->input('term'));
        }
        if ($request->filled('gender') && $request->input('gender') !== 'all') {
            $query->where('graduates.gender', $request->input('gender'));
        }
        if ($request->filled('careerOpportunity') && $request->input('careerOpportunity') !== 'all') {
            $query->where('graduates.current_job_title', $request->input('careerOpportunity'));
        }
        if ($request->filled('program') && $request->input('program') !== 'all') {
            $query->where('graduates.program_id', $request->input('program'));
        }

        $graduates = $query->orderBy('graduates.created_at', 'desc')->paginate(30)->withQueryString();

        // Fetch programs via the pivot table
        $programs = DB::table('institution_programs')
            ->join('programs', 'institution_programs.program_id', '=', 'programs.id')
            ->where('institution_programs.institution_id', $institutionId)
            ->whereNull('programs.deleted_at')
            ->select('programs.*')
            ->get();

        // Unique school years (no duplicates)
        $years = DB::table('institution_school_years')
            ->join('school_years', 'institution_school_years.school_year_range_id', '=', 'school_years.id')
            ->where('institution_school_years.institution_id', $institutionId)
            ->whereNull('school_years.deleted_at')
            ->select('school_years.school_year_range as year_graduated')
            ->distinct()
            ->orderBy('school_years.school_year_range', 'desc')
            ->pluck('year_graduated');

        // Unique terms (no duplicates, for this institution)
        $terms = DB::table('institution_school_years')
            ->where('institution_id', $institutionId)
            ->select('term')
            ->distinct()
            ->pluck('term');

        // Unique career opportunities (no duplicates, for this institution)
        $careerOpportunities = DB::table('institution_career_opportunities')
            ->join('career_opportunities', 'institution_career_opportunities.career_opportunity_id', '=', 'career_opportunities.id')
            ->where('institution_career_opportunities.institution_id', $institutionId)
            ->select('career_opportunities.title')
            ->distinct()
            ->pluck('title');

        return Inertia::render('Graduates/Index', [
            'graduates' => $graduates, // This is now a paginator object
            'programs' => $programs,
            'years' => $years,
            'terms' => $terms,
            'genders' => ['Male', 'Female'],
            'careerOpportunities' => $careerOpportunities,
            'filters' => $request->only(['name', 'year', 'term', 'gender', 'careerOpportunity', 'program']),
        ]);
    }

    public function create()
    {
        $institutionId = Auth::user()->institution->id;

        // Fetch degrees for this institution
        $degrees = DB::table('institution_degrees')
            ->join('degrees', 'institution_degrees.degree_id', '=', 'degrees.id')
            ->where('institution_degrees.institution_id', $institutionId)
            ->select('degrees.id', 'degrees.type')
            ->get();

        // Fetch programs for this institution
        $programs = DB::table('institution_programs')
            ->join('programs', 'institution_programs.program_id', '=', 'programs.id')
            ->where('institution_programs.institution_id', $institutionId)
            ->whereNull('programs.deleted_at')
            ->select('programs.*')
            ->get();

        // Fetch school years for this institution
        $schoolYears = DB::table('institution_school_years')
            ->join('school_years', 'institution_school_years.school_year_range_id', '=', 'school_years.id')
            ->where('institution_school_years.institution_id', $institutionId)
            ->whereNull('school_years.deleted_at')
            ->select('school_years.*')
            ->get();

        return Inertia::render('Auth/GraduateCreate', [
            'degrees' => $degrees,
            'programs' => $programs,
            'schoolYears' => $schoolYears,
        ]);
    }

    public function store(Request $request, CreateNewGraduate $creator)
    {
        $creator->create($request->all());

        return redirect()->route('graduates.index')->with('flash.banner', 'Graduate created successfully with auto-generated credentials.');
    }

    public function update(Request $request, Graduate $graduate)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'program_id' => 'required|exists:programs,id',
            'graduate_year_graduated' => 'required|exists:school_years,school_year_range',
            'employment_status' => 'required|in:Employed,Underemployed,Unemployed',
            'current_job_title' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if (
                        in_array($request->employment_status, ['Employed', 'Underemployed']) &&
                        strtolower(trim($value)) === 'n/a'
                    ) {
                        $fail('Current Job Title cannot be "N/A" when employed or underemployed.');
                    }
                },
            ],
            'dob' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'gender' => 'required|in:Male,Female',
            'contact_number' => 'required|digits_between:10,15|regex:/^9\d{9}$/',
        ]);

        $schoolYearId = DB::table('school_years')
            ->where('school_year_range', $validated['graduate_year_graduated'])
            ->value('id');

        if ($validated['employment_status'] === 'Unemployed') {
            $validated['current_job_title'] = 'N/A';
        }

        $graduate->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'],
            'program_id' => $validated['program_id'],
            'school_year_id' => $schoolYearId,
            'employment_status' => $validated['employment_status'],
            'current_job_title' => $validated['current_job_title'],
            'dob' => $validated['dob'],
            'gender' => $validated['gender'],
            'contact_number' => $validated['contact_number'],
        ]);

        return redirect()->back()->with('flash.banner', 'Graduate updated successfully.');
    }

    public function destroy(Graduate $graduate)
    {
        $graduate->delete();
        return redirect()->back()->with('flash.banner', 'Graduate archived successfully.');
    }

    public function batchPage()
    {
        $institutionId = Auth::user()->institution->id;

        // Fetch degree codes for this institution
        $degreeCodes = DB::table('institution_degrees')
            ->where('institution_id', $institutionId)
            ->pluck('degree_code');

        // Fetch program codes for this institution
        $programCodes = DB::table('institution_programs')
            ->where('institution_id', $institutionId)
            ->pluck('program_code');

        $companyNames = \App\Models\Company::pluck('company_name')->map(fn($n) => strtolower(trim($n)))->toArray();
        $companyNamesMap = \App\Models\Company::all()
            ->mapWithKeys(function ($company) {
                return [strtolower(preg_replace('/\s+/', ' ', trim($company->company_name))) => $company->company_name];
            })
            ->toArray();

        $institutionSchoolYears = \App\Models\InstitutionSchoolYear::where('institution_id', $institutionId)
            ->with('schoolYear')
            ->get()
            ->map(function($item) {
                return [
                    'school_year_range' => $item->schoolYear->school_year_range,
                    'term' => $item->term,
                ];
            })
            ->toArray();

        return Inertia::render('Graduates/BatchUploadPreview', [
            'degreeCodes' => $degreeCodes,
            'programCodes' => $programCodes,
            'companyNames' => $companyNames,
            'companyNamesMap' => $companyNamesMap,
            'institutionSchoolYears' => $institutionSchoolYears,
        ]);
    }

    public function batchUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file, 'r');

        if (!$handle) {
            return response()->json(['status' => 'error', 'message' => 'Unable to read the CSV file.'], 400);
        }

        $delimiter = ',';
        $header = fgetcsv($handle, 0, $delimiter);
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $errors = [];
        $validRows = [];
        $rowNum = 2;
        $institutionId = Auth::user()->institution->id;

        // Fetch all company names (lowercased, trimmed => original)
        $companyNamesMap = \App\Models\Company::all()
            ->mapWithKeys(function ($company) {
                return [strtolower(preg_replace('/\s+/', ' ', trim($company->company_name))) => $company->company_name];
            });

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row = array_combine($header, $data);

            // Skip empty rows
            if (!$row || !is_array($row) || empty(array_filter($row))) {
                $rowNum++;
                continue;
            }

            // Defensive: check for required keys
            if (!isset($row['degree_code']) || !isset($row['program_code'])) {
                $errors[] = [
                    'row' => $rowNum,
                    'messages' => ['Missing degree_code or program_code column in CSV.'],
                ];
                $rowNum++;
                continue;
            }

            $row['degree_code'] = strtoupper(trim($row['degree_code']));
            $row['program_code'] = strtoupper(trim($row['program_code']));
            $row['gender'] = isset($row['gender']) ? ucfirst(strtolower(trim($row['gender']))) : '';
            $row['employment_status'] = isset($row['employment_status']) ? ucfirst(strtolower(trim($row['employment_status']))) : '';

            $validator = Validator::make($row, [
                'email' => 'required|email|unique:users,email',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'degree_code' => 'required|string|max:10',
                'program_code' => 'required|string|max:20',
                'year_graduated' => 'required|exists:school_years,school_year_range',
                'employment_status' => 'required|in:Employed,Underemployed,Unemployed',
                'current_job_title' => 'required_if:employment_status,Employed,Underemployed|nullable|string|max:255',
                'gender' => 'required|in:Male,Female',
                'dob' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
                'contact_number' => 'required|digits_between:10,15|regex:/^9\d{9}$/',
            ]);

            // Convert DOB to Y-m-d if it's in MM/DD/YYYY format
            if (isset($row['dob']) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $row['dob'])) {
                try {
                    $row['dob'] = Carbon::createFromFormat('m/d/Y', $row['dob'])->format('Y-m-d');
                } catch (\Exception $e) {
                    // If conversion fails, mark as invalid
                    $errors[] = [
                        'row' => $rowNum,
                        'messages' => ['Invalid date format for DOB.'],
                    ];
                    $rowNum++;
                    continue;
                }
            }

            if (
                in_array($row['employment_status'], ['Employed', 'Underemployed']) &&
                strtolower(trim($row['current_job_title'])) === 'n/a'
            ) {
                $errors[] = [
                    'row' => $rowNum,
                    'messages' => ['Current Job Title cannot be "N/A" when employed or underemployed.'],
                ];
                $rowNum++;
                continue;
            }

            // Company Name Validation
            $employmentStatus = strtoupper(trim($row['employment_status'] ?? ''));
            $inputCompanyName = trim($row['company_name'] ?? '');

            if ($employmentStatus === 'UNEMPLOYED') {
                if (strtoupper($inputCompanyName) !== 'N/A') {
                    $errors[] = [
                        'row' => $rowNum,
                        'messages' => ['For unemployed graduates, company name must be "N/A".'],
                    ];
                    $rowNum++;
                    continue;
                }
                $row['company_name'] = 'N/A';
                $row['company_id'] = null;
            } else {
                // Must match a company (case-insensitive, ignore extra spaces)
                $normalized = strtolower(preg_replace('/\s+/', ' ', $inputCompanyName));
                if (!$inputCompanyName) {
                    $errors[] = [
                        'row' => $rowNum,
                        'messages' => ['Company name is required for employed/underemployed graduates.'],
                    ];
                    $rowNum++;
                    continue;
                }
                if (!isset($companyNamesMap[$normalized])) {
                    $errors[] = [
                        'row' => $rowNum,
                        'messages' => ["Company name '{$inputCompanyName}' does not exist in the system."],
                    ];
                    $rowNum++;
                    continue;
                }
                // Use the exact format from the database
                $row['company_name'] = $companyNamesMap[$normalized];
                $row['company_id'] = \App\Models\Company::where('company_name', $companyNamesMap[$normalized])->value('id');
            }

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $rowNum,
                    'messages' => $validator->errors()->all(),
                ];
            } else {
                $validRows[] = $row;
            }

            $rowNum++;
        }

        fclose($handle);

        if (count($errors)) {
            return response()->json([
                'status' => 'error',
                'errors' => $errors,
            ], 422);
        }

        foreach ($validRows as $row) {
            $password = $this->generatePassword($row['last_name'], $row['dob']);

            // Add these lines:
            $degreeId = $this->getDegreeIdByCode($row['degree_code'], $institutionId);
            $programId = $this->getProgramIdByCode($row['program_code'], $institutionId);

            if (!$degreeId) {
                $errors[] = [
                    'row' => $rowNum,
                    'messages' => ["Degree code '{$row['degree_code']}' does not exist for this institution."],
                ];
                $rowNum++;
                continue;
            }

            if (!$programId) {
                $errors[] = [
                    'row' => $rowNum,
                    'messages' => ["Program code '{$row['program_code']}' does not exist for this institution."],
                ];
                $rowNum++;
                continue;
            }

            $user = User::create([
                'email' => $row['email'],
                'password' => Hash::make($password),
                'role' => 'graduate',
                'is_approved' => true,
            ]);

            DB::table('graduates')->insert([
                'user_id' => $user->id,
                'first_name' => $this->toTitleCase($row['first_name']),
                'middle_name' => $this->toTitleCase($row['middle_name']),
                'last_name' => $this->toTitleCase($row['last_name']),
                'institution_id' => $institutionId,
                'degree_id' => $degreeId,
                'program_id' => $programId,
                'school_year_id' => $this->getInstitutionSchoolYearId($row['year_graduated'], $row['term'], $institutionId),
                'employment_status' => $row['employment_status'],
                'current_job_title' => in_array($row['employment_status'], ['Employed', 'Underemployed'])
                    ? $this->toTitleCase($row['current_job_title'])
                    : 'N/A',
                'gender' => $row['gender'],
                'dob' => $row['dob'],
                'contact_number' => $row['contact_number'],
                'company_id' => $row['company_id'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user->assignRole('graduate');
        }

        return response()->json(['status' => 'success']);
    }

    public function downloadTemplate()
    {
        return response()->download(public_path('templates/graduate_template.csv'));
    }

    public function archivedList()
    {
        $institutionId = Auth::user()->institution->id;

        $graduates = DB::table('graduates')
            ->join('users', 'graduates.user_id', '=', 'users.id')
            ->join('programs', 'graduates.program_id', '=', 'programs.id')
            ->join('school_years', 'graduates.school_year_id', '=', 'school_years.id')
            ->where('users.role', 'graduate')
            ->where('graduates.institution_id', $institutionId)
            ->where('users.is_approved', true)
            ->whereNotNull('graduates.deleted_at')
            ->select(
                'graduates.id',
                'graduates.first_name',
                'graduates.last_name',
                'graduates.middle_name',
                'programs.name as graduate_program_completed',
                'graduates.created_at'
            )
            ->orderBy('graduates.deleted_at', 'desc')
            ->get();

        return Inertia::render('Graduates/ArchivedList', [
            'graduates' => $graduates,
        ]);
    }

    public function restore($id)
    {
        $graduate = Graduate::withTrashed()->findOrFail($id);
        $graduate->restore();
        return redirect()->back()->with('flash.banner', 'Graduate restored successfully.');
    }

    private function getProgramId($programName)
    {
        return DB::table('programs')->where('name', $programName)->value('id');
    }

    private function getYearId($schoolYearRange)
    {
        return DB::table('school_years')->where('school_year_range', $schoolYearRange)->value('id');
    }

    private function generatePassword($lastName, $dob)
    {
        $year = Carbon::parse($dob)->year;
        $lastNameTitle = $this->toTitleCase($lastName);
        return "{$lastNameTitle}{$year}!!!";
    }

    private function getDegreeIdByCode($degreeCode, $institutionId)
    {
        return DB::table('institution_degrees')
            ->where('institution_id', $institutionId)
            ->where('degree_code', $degreeCode)
            ->value('degree_id');
    }

    private function getProgramIdByCode($programCode, $institutionId)
    {
        return DB::table('institution_programs')
            ->where('institution_id', $institutionId)
            ->where('program_code', $programCode)
            ->value('program_id');
    }

    private function toTitleCase($string)
    {
        return mb_convert_case(mb_strtolower(trim($string)), MB_CASE_TITLE, "UTF-8");
    }

    private function getInstitutionSchoolYearId($schoolYearRange, $term, $institutionId)
    {
        $schoolYearId = \DB::table('school_years')
            ->where('school_year_range', $schoolYearRange)
            ->value('id');

        return \DB::table('institution_school_years')
            ->where('institution_id', $institutionId)
            ->where('school_year_range_id', $schoolYearId)
            ->where('term', $term)
            ->value('id');
    }
}
