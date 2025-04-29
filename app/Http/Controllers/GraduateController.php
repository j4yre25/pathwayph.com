<?php

namespace App\Http\Controllers;

use App\Models\Graduate;
use App\Models\Program;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\Writer;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GraduateController extends Controller
{
    public function index()
    {
        return Inertia::render('Graduates/Index', [
            'graduates' => Graduate::with(['program', 'schoolYear'])->get()->map(function ($grad) {
                return [
                    'id' => $grad->id,
                    'first_name' => $grad->first_name,
                    'last_name' => $grad->last_name,
                    'middle_initial' => $grad->middle_initial,
                    'full_name' => $grad->full_name,
                    'program_name' => $grad->program->name ?? 'N/A',
                    'year_graduated' => $grad->schoolYear->school_year_range ?? 'N/A',
                    'employment_status' => $grad->employment_status,
                    'current_job_title' => $grad->current_job_title,
                ];
            }),
            'programs' => Program::all(),
            'years' => SchoolYear::select('id', 'school_year_range')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:5',
            'program_id' => 'required|exists:programs,id',
            'school_year_id' => 'required|exists:school_years,id',
            'employment_status' => 'required|in:Employed,Underemployed,Unemployed',
            'current_job_title' => 'nullable|string|max:255',
        ]);

        if ($validated['employment_status'] === 'Unemployed') {
            $validated['current_job_title'] = 'N/A';
        }

        Graduate::create($validated);

        return redirect()->route('graduates.index')->with('success', 'Graduate added successfully.');
    }

    public function update(Request $request, Graduate $graduate)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:5',
            'program_id' => 'required|exists:programs,id',
            'school_year_id' => 'required|exists:school_years,id',
            'employment_status' => 'required|in:Employed,Underemployed,Unemployed',
            'current_job_title' => 'nullable|string|max:255',
        ]);

        if ($validated['employment_status'] === 'Unemployed') {
            $validated['current_job_title'] = 'N/A';
        }

        $graduate->update($validated);

        return redirect()->route('graduates.index')->with('success', 'Graduate updated successfully.');
    }

    public function destroy(Graduate $graduate)
    {
        $graduate->delete();
        return redirect()->route('graduates.index')->with('success', 'Graduate archived successfully.');
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'First Name',
            'Middle Initial',
            'Last Name',
            'Program',
            'Year Graduated',
            'Employment Status',
            'Current Job Title'
        ];

        $rows = [
            ['John', 'A', 'Doe', 'Computer Science', '2020-2021', 'Employed', 'Software Engineer'],
            ['Jane', '', 'Smith', 'Business Administration', '2019-2020', 'Employed', 'Marketing Manager'],
            ['Michael', 'B', 'Brown', 'Mechanical Engineering', '2021-2022', 'Underemployed', 'Junior Designer'],
            ['Emily', '', 'Johnson', 'Nursing', '2022-2023', 'Unemployed', 'N/A']
        ];

        $csv = Writer::createFromString('');
        $csv->insertOne($headers);
        $csv->insertAll($rows);

        return Response::streamDownload(function () use ($csv) {
            echo $csv;
        }, 'graduate_template.csv');
    }

    public function batchUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $csv->setHeaderOffset(0); // Use the first row as header
        $records = Statement::create()->process($csv);

        foreach ($records as $record) {
            try {
                $program = Program::where('name', $record['Program'])->first();
                $schoolYear = SchoolYear::where('school_year_range', $record['Year Graduated'])->first();

                if (!$program || !$schoolYear) {
                    continue; // skip if invalid foreign key match
                }

                Graduate::create([
                    'first_name' => $record['First Name'],
                    'middle_initial' => $record['Middle Initial'] ?? null,
                    'last_name' => $record['Last Name'],
                    'program_id' => $program->id,
                    'school_year_id' => $schoolYear->id,
                    'employment_status' => $record['Employment Status'],
                    'current_job_title' => $record['Employment Status'] === 'Unemployed' ? 'N/A' : $record['Current Job Title'],
                ]);
            } catch (\Exception $e) {
                Log::error('CSV row skipped due to error: ' . $e->getMessage());
                continue;
            }
        }

        return redirect()->route('graduates.index')->with('success', 'Graduates uploaded successfully.');
    }


    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName));
        $first = array_shift($parts) ?? '';
        $last = array_pop($parts) ?? '';
        $middle = count($parts) > 0 ? implode(' ', $parts) : null;

        return [$first, $middle, $last];
    }

}
