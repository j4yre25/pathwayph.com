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
            ->select(
                'users.id as user_id',
                DB::raw("CONCAT(users.graduate_first_name, ' ', users.graduate_middle_initial, ' ', users.graduate_last_name) as full_name"),
                'programs.name as program_name',
                'school_years.school_year_range as year_graduated',
                'graduates.current_job_title',
                DB::raw("'Yes' as graduated")
            )
            ->orderBy('users.created_at', 'desc')
            ->get();

        $institutionPrograms = Program::where('institution_id', Auth::user()->id)->get();
        $institutionYears = SchoolYear::where('institution_id', Auth::user()->id)->get();
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
            'programs' => Program::where('institution_id', Auth::user()->id)->get(),
            'schoolYears' => SchoolYear::where('institution_id', Auth::user()->id)->get(),
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
            'middle_initial' => 'required|string|max:5',
            'program_id' => 'required|exists:programs,id',
            'graduate_year_graduated' => 'required|exists:school_years,school_year_range',
            'employment_status' => 'required|in:Employed,Underemployed,Unemployed',
            'current_job_title' => 'nullable|string|max:255',
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
            'middle_initial' => $validated['middle_initial'],
            'program_id' => $validated['program_id'],
            'school_year_id' => $schoolYearId,
            'employment_status' => $validated['employment_status'],
            'current_job_title' => $validated['current_job_title'],
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
        $institutionId = auth()->id();

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
                'gender' => 'required|in:Male,Female,Other',
                'dob' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
                'contact_number' => 'required|digits_between:10,15|regex:/^9\d{9}$/',
            ]);

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
