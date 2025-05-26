<?php

namespace App\Http\Controllers;

use App\Models\Graduate;
use App\Models\Program;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Actions\Fortify\CreateNewGraduate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class GraduateController extends Controller
{
    public function index()
    {
        $graduates = DB::table('graduates')
            ->join('users', 'graduates.user_id', '=', 'users.id')
            ->join('programs', 'graduates.program_id', '=', 'programs.id')
            ->join('school_years', 'graduates.school_year_id', '=', 'school_years.id')
            ->where('users.role', 'graduate')
            ->where('users.institution_id', Auth::user()->id)
            ->where('users.is_approved', true)
            ->whereNull('graduates.deleted_at') // <-- Only show non-archived graduates
            ->select(
                'users.id as user_id',
                'users.email',
                'users.graduate_first_name as first_name',
                'users.graduate_last_name as last_name',
                'users.graduate_middle_initial as middle_initial',
                'users.gender',
                'users.dob',
                'users.contact_number',
                'graduates.id as graduate_id',
                'graduates.program_id',
                'graduates.school_year_id',
                'programs.name as program_name',
                'school_years.school_year_range as year_graduated',
                'graduates.current_job_title',
                'graduates.employment_status',
                DB::raw("CONCAT(users.graduate_first_name, ' ', IFNULL(users.graduate_middle_initial, ''), ' ', users.graduate_last_name) as full_name"),
                DB::raw("'Yes' as graduated")
            )
            ->orderBy('users.created_at', 'desc')
            ->get();

        $institutionPrograms = \App\Models\InstitutionProgram::with('program')
            ->where('institution_id', Auth::user()->id)
            ->get()
            ->map(function ($ip) {
                return [
                    'id' => $ip->program->id,
                    'name' => $ip->program->name,
                    'institution_id' => $ip->institution_id,
                ];
            });
            
        $institutionYears = \App\Models\InstitutionSchoolYear::with('schoolYear')
            ->where('institution_id', Auth::user()->id)
            ->get()
            ->map(function ($isy) {
                return [
                    'id' => $isy->schoolYear->id,
                    'school_year_range' => $isy->schoolYear->school_year_range,
                    'institution_id' => $isy->institution_id,
                ];
            });
        $instiUsers = DB::table('users')
            ->where('role', 'institution')
            ->where('id', Auth::user()->id)
            ->get(['id', 'institution_name']);

        return Inertia::render('Graduates/Index', [
            'graduates' => $graduates,
            'programs' => $institutionPrograms,
            'years' => $institutionYears,
            'instiUsers' => $instiUsers,
        ]);
    }

    public function create()
    {
        return Inertia::render('Auth/GraduateCreate', [
            'programs' => \App\Models\InstitutionProgram::with('program')
                ->where('institution_id', Auth::user()->id)
                ->get()
                ->map(function ($ip) {
                    return [
                        'id' => $ip->program->id,
                        'name' => $ip->program->name,
                        'institution_id' => $ip->institution_id,
                    ];
                }),
            'schoolYears' => \App\Models\InstitutionSchoolYear::with('schoolYear')
                ->where('institution_id', Auth::user()->id)
                ->get()
                ->map(function ($isy) {
                    return [
                        'id' => $isy->schoolYear->id,
                        'school_year_range' => $isy->schoolYear->school_year_range,
                        'institution_id' => $isy->institution_id,
                    ];
                }),
            'institutions' => DB::table('users')
                ->where('role', 'institution')
                ->where('id', Auth::user()->id)
                ->get(['id', 'institution_name']),
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
            'middle_initial' => 'nullable|string|max:5',
            'program_id' => 'required|exists:programs,id',
            'graduate_year_graduated' => 'required|exists:school_years,school_year_range',
            'employment_status' => 'required|in:Employed,Underemployed,Unemployed',
            'current_job_title' => [
                'nullable',
                'string',
                'max:255',
                // Custom rule: if employed/underemployed, must not be N/A or n/a
                function ($attribute, $value, $fail) use ($request) {
                    if (
                        in_array($request->employment_status, ['Employed', 'Underemployed']) &&
                        strtolower(trim($value)) === 'n/a'
                    ) {
                        $fail('Current Job Title cannot be "N/A" when employed or underemployed.');
                    }
                },
            ],
        ]);

        $schoolYearId = DB::table('school_years')
            ->where('school_year_range', $validated['graduate_year_graduated'])
            ->value('id');

        if ($validated['employment_status'] === 'Unemployed') {
            $validated['current_job_title'] = 'N/A';
        }

        // Update Graduate table
        $graduate->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_initial' => $validated['middle_initial'],
            'program_id' => $validated['program_id'],
            'school_year_id' => $schoolYearId,
            'employment_status' => $validated['employment_status'],
            'current_job_title' => $validated['current_job_title'],
        ]);

        // Update User table
        $user = $graduate->user;
        $user->update([
            'graduate_first_name' => $validated['first_name'],
            'graduate_last_name' => $validated['last_name'],
            'graduate_middle_initial' => $validated['middle_initial'],
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
        return Inertia::render('Graduates/BatchUploadPreview');
    }

    public function batchUpload(Request $request): JsonResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file, 'r');

        if (!$handle) {
            return response()->json(['status' => 'error', 'message' => 'Unable to read the CSV file.'], 400);
        }

        // Specify the delimiter (e.g., ',' for comma, ';' for semicolon, '\t' for tab)
        $delimiter = ','; // Change this to match your CSV file's delimiter
        $header = fgetcsv($handle, 0, $delimiter);
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }
        $header = array_map(fn($h) => strtolower(trim($h)), $header); // Normalize headers


        $errors = [];
        $validRows = [];
        $rowNum = 2; // Start from second row (after header)
        $institutionId = Auth::user()->id;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row = array_combine($header, $data);


                if (!empty($row['dob'])) {
            try {
                $row['dob'] = Carbon::createFromFormat('m/d/Y', $row['dob'])->format('Y-m-d');
            } catch (\Exception $e) {
                $errors[] = [
                    'row' => $rowNum,
                    'messages' => ['Invalid date format for DOB. Expected format: MM/DD/YYYY.'],
                ];
                $rowNum++;
                continue;
            }
        }

            $validator = Validator::make($row, [
                'email' => 'required|email|unique:users,email',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_initial' => 'required|string|max:5',
                'program_completed' => 'required|exists:programs,name',
                'year_graduated' => 'required|exists:school_years,school_year_range',
                'employment_status' => 'required|in:Employed,Underemployed,Unemployed',
                'current_job_title' => 'required_if:employment_status,Employed,Underemployed|nullable|string|max:255',
                'gender' => 'required|in:Male,Female',
                'dob' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
                'contact_number' => 'required|digits_between:10,15|regex:/^9\d{9}$/',
            ]);

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

            $user = User::create([
                'email' => $row['email'],
                'password' => Hash::make($password),
                'role' => 'graduate',
                'is_approved' => true,
                'gender' => $row['gender'],
                'dob' => $row['dob'],
                'contact_number' => $row['contact_number'],
                'graduate_first_name' => $row['first_name'],
                'graduate_last_name' => $row['last_name'],
                'graduate_middle_initial' => $row['middle_initial'],
                'graduate_school_graduated_from' => $institutionId,
                'graduate_program_completed' => $this->getProgramId($row['program_completed']),
                'graduate_year_graduated' => $this->getYearId($row['year_graduated']),
                'institution_id' => $institutionId,
            ]);

            DB::table('graduates')->insert([
                'user_id' => $user->id,
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'middle_initial' => $row['middle_initial'],
                'institution_id' => $institutionId,
                'program_id' => $this->getProgramId($row['program_completed']),
                'school_year_id' => $this->getYearId($row['year_graduated']),
                'employment_status' => $row['employment_status'],
                'current_job_title' => in_array($row['employment_status'], ['Employed', 'Underemployed']) ? $row['current_job_title'] : 'N/A',
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
        $graduates = DB::table('graduates')
            ->join('users', 'graduates.user_id', '=', 'users.id')
            ->join('programs', 'graduates.program_id', '=', 'programs.id')
            ->join('school_years', 'graduates.school_year_id', '=', 'school_years.id')
            ->where('users.role', 'graduate')
            ->where('users.institution_id', Auth::user()->id)
            ->where('users.is_approved', true)
            ->whereNotNull('graduates.deleted_at') // <-- Only show archived graduates
            ->select(
                'graduates.id',
                'graduates.first_name',
                'graduates.last_name',
                'graduates.middle_initial',
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
        return "{$lastName}{$year}!!!";
    }
}
