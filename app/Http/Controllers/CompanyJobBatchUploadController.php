<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JobType;
use App\Models\Department;
use App\Models\Sector;
use App\Models\Category;
use App\Models\Program;
use App\Models\Location;
use App\Models\WorkEnvironment;
use App\Services\JobCreationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class CompanyJobBatchUploadController extends Controller
{
    // 1. Batch Upload Preview Page
    public function batchPage()
    {
        return Inertia::render('Company/Jobs/Index/BatchUploadPreview');
    }
    function fuzzyFind($model, $column, $value) {
        if (!$value) return null;
        // Try exact match (case-insensitive)
        $match = $model::whereRaw("LOWER($column) = ?", [strtolower(trim($value))])->first();
        if ($match) return $match;
        // Try partial match
        $match = $model::where($column, 'LIKE', '%' . trim($value) . '%')->first();
        if ($match) return $match;
        // Try removing spaces and compare
        $match = $model::whereRaw("REPLACE(LOWER($column), ' ', '') = ?", [str_replace(' ', '', strtolower(trim($value)))])->first();
        return $match;
    }

    // 2. Batch Upload Handler
    public function batchUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        $extension = $request->file('csv_file')->getClientOriginalExtension();

        if (in_array($extension, ['xlsx', 'xls'])) {
            // Parse Excel
            $rows = Excel::toArray([], $request->file('csv_file'))[0];
            $header = array_map(fn($h) => strtolower(trim($h)), $rows[0]);
            $dataRows = array_slice($rows, 1);
        } else {
            // Parse CSV as before
            $file = $request->file('csv_file');
            $handle = fopen($file, 'r');
            $header = fgetcsv($handle, 0, ',');
            if (isset($header[0])) {
                $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            }
            $header = array_map(fn($h) => strtolower(trim($h)), $header);
            $dataRows = [];
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                $dataRows[] = $data;
            }
            fclose($handle); // <-- move here!
        }

        $errors = [];
        $validRows = [];
        $rowNum = 2;

        foreach ($dataRows as $data) {
        $row = array_combine($header, $data);

            // Skip empty rows
            if (!$row || !is_array($row) || empty(array_filter($row))) {
                $rowNum++;
                continue;
            }

            // --- Department ---
            $row['department_id'] = null;
            if (!empty($row['department_name'])) {
                $department = $this->fuzzyFind(Department::class, 'department_name', $row['department_name']);
                $row['department_id'] = $department ? $department->id : null;
            }

            // --- Sector ---
            $row['sector'] = null;
            if (!empty($row['sector_name'])) {
                $sector = $this->fuzzyFind(Sector::class, 'name', $row['sector_name']);
                $row['sector'] = $sector ? $sector->id : null;
            }

            // --- Category ---
            $row['category'] = null;
            if (!empty($row['category_name']) && !empty($row['sector'])) {
                $category = Category::where('sector_id', $row['sector'])
                    ->whereRaw('LOWER(name) = ?', [strtolower(trim($row['category_name']))])
                    ->first();
                if (!$category) {
                    // Try partial/fuzzy match within sector
                    $category = Category::where('sector_id', $row['sector'])
                        ->where('name', 'LIKE', '%' . trim($row['category_name']) . '%')
                        ->first();
                }
                $row['category'] = $category ? $category->id : null;
            }

            // --- Program ---
            $row['program_id'] = [];
            if (!empty($row['program_name'])) {
                // Support multiple programs separated by comma
                $programNames = array_map('trim', explode(',', $row['program_name']));
                foreach ($programNames as $progName) {
                    $program = $this->fuzzyFind(Program::class, 'name', $progName);
                    if ($program) {
                        $row['program_id'][] = $program->id;
                    }
                }
            }

            // --- Work Environment ---
            $row['work_environment'] = null;
            if (!empty($row['work_environment_type'])) {
                $workEnv = $this->fuzzyFind(WorkEnvironment::class, 'environment_type', $row['work_environment_type']);
                $row['work_environment'] = $workEnv ? $workEnv->id : null;
            }

            // --- Job Types (comma-separated) ---
            $jobTypeIds = [];
            if (!empty($row['job_types'])) {
                $typeNames = array_map('trim', explode(',', $row['job_types']));
                foreach ($typeNames as $typeName) {
                    $jobType = $this->fuzzyFind(JobType::class, 'type', $typeName);
                    if ($jobType) {
                        $jobTypeIds[] = $jobType->id;
                    }
                }
            }
            $row['job_type_ids'] = $jobTypeIds;
            $row['job_type'] = !empty($jobTypeIds) ? $jobTypeIds[0] : null; // for main field

            // --- Job Experience Level (enum) ---
            $allowed = [
                'Entry-level',
                'Intermediate',
                'Mid-level',
                'Senior/Executive'
            ];
            $input = strtolower(trim($row['job_experience_level'] ?? ''));
            $matched = collect($allowed)->first(function($level) use ($input) {
                $levelLower = strtolower($level);
                return $levelLower === $input
                    || str_contains($levelLower, $input)
                    || str_contains($input, $levelLower)
                    || preg_replace('/[^a-z]/', '', $levelLower) === preg_replace('/[^a-z]/', '', $input);
            });
            $row['job_experience_level'] = $matched ?? $allowed[0];

            // Prepare skills as array
            if (!empty($row['skills'])) {
                $row['skills'] = array_map('trim', explode(',', $row['skills']));
            }

            // --- job_application_limit ---
            $row['job_application_limit'] = isset($row['job_application_limit']) && $row['job_application_limit'] !== '' ? (int)$row['job_application_limit'] : null;

            $validator = Validator::make($row, [
                'job_title' => 'required|string|max:255',
                'department_id' => 'nullable|exists:departments,id',
                'job_description' => 'nullable|string',
                'job_requirements' => 'nullable|string',
                'job_min_salary' => 'nullable|numeric',
                'job_max_salary' => 'nullable|numeric',
                'location' => 'nullable|string|max:255',
                'work_environment' => 'nullable|exists:work_environments,id',
                'program_id' => 'nullable|array',
                'program_id.*' => 'exists:programs,id',
                'category_id' => 'nullable|exists:categories,id',
                'sector_id' => 'nullable|exists:sectors,id',
                'job_experience_level' => 'nullable|string|max:255',
                'status' => 'nullable|string|max:255',
                'is_approved' => 'nullable|boolean',
                'skills' => 'nullable|array',
                'job_application_limit' => 'nullable|integer|min:1',
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


        if (count($errors)) {
            return response()->json([
                'status' => 'error',
                'errors' => $errors,
            ], 422);
        }

        $user = auth()->user();
        $jobService = new JobCreationService();

        foreach ($validRows as $row) {
            // Convert 'is_negotiable' from 'yes'/'blank' to 1/0
            if (isset($row['is_negotiable']) && strtolower(trim($row['is_negotiable'])) === 'yes') {
                $row['is_negotiable'] = 1;
            } else {
                $row['is_negotiable'] = 0;
            }

            // Convert job_deadline to Y-m-d if present and not empty
            if (!empty($row['job_deadline'])) {
                try {
                    $row['job_deadline'] = Carbon::parse($row['job_deadline'])->format('Y-m-d');
                } catch (\Exception $e) {
                    $row['job_deadline'] = null;
                }
            } else {
                $row['job_deadline'] = null;
            }

            // Prepare salary array for the service
            $row['salary'] = [
                'job_min_salary' => $row['job_min_salary'] ?? null,
                'job_max_salary' => $row['job_max_salary'] ?? null,
                'salary_type' => $row['salary_type'] ?? 'Monthly',
            ];

            // Set defaults if missing
            $row['status'] = $row['status'] ?? 'pending';
            $row['is_approved'] = $row['is_approved'] ?? null;

            // Call the service
            $job = $jobService->createJob($row, $user);

            // Attach all job types if multiple
            if (!empty($row['job_type_ids'])) {
                $job->jobTypes()->sync($row['job_type_ids']);
            }
        }

        return redirect()
        ->route('company.jobs', ['user' => auth()->id()])
        ->with('flash.banner', 'Batch upload successful! All jobs have been posted.');
    }

    // 3. Download CSV Template
    public function downloadTemplate()
    {
        return response()->download(public_path('templates/job_template.xlsx'));
    }
}