<?php


namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Carbon\Carbon;

class CompanyBatchUploadController extends Controller
{
    // 1. Batch Upload Preview Page
    public function batchPage()
    {
        return Inertia::render('Admin/ManageUsers/Index/BatchUploadPreview');
    }

    // 2. Batch Upload Handler
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

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row = array_combine($header, $data);

            // Skip empty rows
            if (!$row || !is_array($row) || empty(array_filter($row))) {
                $rowNum++;
                continue;
            }

            if (!empty($row['sector_name'])) {
                $sector = \App\Models\Sector::where('name', $row['sector_name'])->first();
                $row['sector_id'] = $sector ? $sector->id : null;
            } else {
                $row['sector_id'] = null;
            }

            if (!empty($row['category_name'])) {
                $category = \App\Models\Category::where('name', $row['category_name'])->first();
                $row['category_id'] = $category ? $category->id : null;
            } else {
                $row['category_id'] = null;
            }

            $validator = Validator::make($row, [
                'email' => 'required|email|unique:users,email',
                'company_name' => 'required|string|max:255',
                'company_contact_number' => 'nullable|string|max:20',
                'company_email' => 'nullable|email|max:255',
                'company_street_address' => 'nullable|string|max:255',
                'company_city' => 'nullable|string|max:255',
                'company_province' => 'nullable|string|max:255',
                'company_zip_code' => 'nullable|string|max:10',
                'company_description' => 'nullable|string|max:1000',
                'sector_id' => 'required|exists:sectors,id',
                'category_id' => 'required|exists:categories,id',
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

        // Insert valid companies
        foreach ($validRows as $row) {
            $password = $this->generatePassword($row['company_name']);

            $user = User::create([
                'email' => $row['email'],
                'password' => Hash::make($password),
                'role' => 'company',
                'is_approved' => true,
                'has_completed_information' => true,
            ]);

            $company = Company::create([
                'user_id' => $user->id,
                'company_name' => $row['company_name'],
                'company_contact_number' => $row['company_contact_number'] ?? null,
                'company_email' => $row['company_email'] ?? null,
                'company_street_address' => $row['company_street_address'] ?? null,
                'company_city' => $row['company_city'] ?? null,
                'company_province' => $row['company_province'] ?? null,
                'company_zip_code' => $row['company_zip_code'] ?? null,
                'description' => $row['company_description'] ?? null,
                'sector_id' => $row['sector_id'],
                'category_id' => $row['category_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Generate company_id code
            $paddedCompanyId = str_pad($company->id, 3, '0', STR_PAD_LEFT);
            $sectorCode = $company->sector->sector_id ?? '000';
            $divisionCode = $company->category->division_code ?? '00';
            $company->company_id = "C-{$paddedCompanyId}-{$sectorCode}{$divisionCode}";
            $company->save();

            $user->assignRole('company');
        }

        return response()->json(['status' => 'success']);
    }

    // 3. Download CSV Template
    public function downloadTemplate()
    {
        return response()->download(public_path('templates/company_template.csv'));
    }

    // Helper: Generate a default password
    private function generatePassword($companyName)
    {
        $name = preg_replace('/\s+/', '', strtolower($companyName));
        return $name . '@2024!';
    }
}