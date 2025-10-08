<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\HumanResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ManageUsersController extends Controller
{
    public function index(Request $request)
    {
        // Build the base query with relationships
        $query = User::with([
            'company.sector',
            'institution',
            'peso',
        ])
            ->whereIn('role', ['company', 'institution', 'peso'])
            ->where('has_completed_information', 1);

        // Apply filters
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            // Assuming 'active' means is_approved = 1, 'inactive' means is_approved = 0
            $query->where('is_approved', $request->status === 'active' ? 1 : 0);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Clone for KPI counts
        $filteredQuery = (clone $query);

        // Paginate
        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Transform users for display (keep your switch logic)
        $users->getCollection()->transform(function ($user) {
            switch ($user->role) {
                case 'company':
                    $user->full_name = $user->hr
                        ? trim("{$user->hr->first_name} " . ($user->hr->middle_name ? "{$user->hr->middle_name} " : "") . "{$user->hr->last_name}")
                        : 'N/A';
                    $user->organization_name = ($user->company && !empty($user->company->company_name))
                        ? $user->company->company_name
                        : '-';
                    break;
                case 'institution':
                    $user->full_name = $user->institution
                        ? trim("{$user->institution->institution_president_first_name} {$user->institution->institution_president_last_name}")
                        : 'N/A';
                    $user->organization_name = $user->institution->institution_name ?? '-';
                    break;
                case 'peso':
                    $user->full_name = $user->peso
                        ? trim("{$user->peso->peso_first_name} {$user->peso->peso_last_name}")
                        : 'N/A';
                    $user->organization_name = $user->peso->peso_name ?? '-';
                    break;
                case 'graduate':
                    $user->full_name = $user->graduate
                        ? trim("{$user->graduate->first_name} {$user->graduate->last_name}")
                        : 'N/A';
                    $user->organization_name = '-';
                    break;
                default:
                    $user->full_name = 'N/A';
                    $user->organization_name = '-';
            }
            return $user;
        });

        // KPIs based on filtered users
        $totalUsers = (clone $filteredQuery)->count();
        $approvedCount = (clone $filteredQuery)->where('is_approved', 1)->count();
        $pendingCount = (clone $filteredQuery)->whereNull('is_approved')->count();
        $disapprovedCount = (clone $filteredQuery)->where('is_approved', 0)->count();

        return Inertia::render('Admin/ManageUsers/Index/Index', [
            'all_users' => $users,
            'kpi' => [
                'total' => $totalUsers,
                'approved' => $approvedCount,
                'pending' => $pendingCount,
                'disapproved' => $disapprovedCount,
            ],
        ]);
    }

    public function apiUser($id)
    {
        $user = User::with([
            'company.sector',
            'company.category',
            'hr',
            'institution',
            'peso',
        ])->findOrFail($id);

        return response()->json(['user' => $user]);
    }

    public function list(Request $request)
    {
        $users = User::with(['company', 'institution', 'peso'])
            ->where('role', '!=', 'graduate')
            ->where('has_completed_information', 1)
            ->when($request->role && $request->role !== 'all', function ($query) use ($request) {
                $query->where('role', $request->role);
            })
            ->when($request->date_from, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->date_to, function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->when($request->status && $request->status !== 'all', function ($query) use ($request) {
                // Use 1 for active, 0 for inactive
                $query->where('is_approved', $request->status === 'active' ? 1 : 0);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $users->getCollection()->transform(function ($user) {
            switch ($user->role) {
                case 'company':
                    $user->full_name = $user->hr
                        ? trim("{$user->hr->first_name} " . ($user->hr->middle_name ? "{$user->hr->middle_name} " : "") . "{$user->hr->last_name}")
                        : 'N/A';
                    $user->organization_name = $user->company->company_name ?? '-';
                    break;
                case 'institution':
                    $user->full_name = $user->institution
                        ? trim("{$user->institution->institution_president_first_name} {$user->institution->institution_president_last_name}")
                        : 'N/A';
                    $user->organization_name = $user->institution->institution_name ?? '-';
                    break;
                case 'peso':
                    $user->full_name = $user->peso
                        ? trim("{$user->peso->peso_first_name} {$user->peso->peso_last_name}")
                        : 'N/A';
                    $user->organization_name = $user->peso->peso_name ?? '-';
                    break;
                default:
                    $user->full_name = 'N/A';
                    $user->organization_name = '-';
            }
            return $user;
        });

        return inertia('Admin/ManageUsers/Index/List', [
            'all_users' => $users,

        ]);
    }

    public function archivedlist()
    {
        $users = User::with([
            'company',
            'institution',
            'peso',
            'graduate',
        ])
            ->onlyTrashed()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $users->getCollection()->transform(function ($user) {
            switch ($user->role) {
                case 'company':
                    $user->full_name = $user->company
                        ? trim("{$user->company->first_name} {$user->company->last_name}")
                        : 'N/A';
                    $user->organization_name = $user->company->company_name ?? '-';
                    break;
                case 'institution':
                    $user->full_name = $user->institution
                        ? trim("{$user->institution->institution_president_first_name} {$user->institution->institution_president_last_name}")
                        : 'N/A';
                    $user->organization_name = $user->institution->institution_name ?? '-';
                    break;
                case 'peso':
                    $user->full_name = $user->peso
                        ? trim("{$user->peso->peso_first_name} {$user->peso->peso_last_name}")
                        : 'N/A';
                    $user->organization_name = $user->peso->peso_name ?? '-';
                    break;
                case 'graduate':
                    $user->full_name = $user->graduate
                        ? trim("{$user->graduate->first_name} {$user->graduate->last_name}")
                        : 'N/A';
                    $user->organization_name = '-';
                    break;
                default:
                    $user->full_name = 'N/A';
                    $user->organization_name = '-';
            }
            return $user;
        });

        return Inertia::render('Admin/ManageUsers/Index/ArchivedList', [
            'all_users' => $users
        ]);
    }

    public function edit(User $users)
    {
        return Inertia::render('Admin/ManageUsers/Edit/Index', [
            'user' => $users
        ]);
    }



    public function approve(User $user)
    {
        $user->is_approved = true;
        $user->save();

        // If company, set as main HR
        if ($user->role === 'company' && $user->hr) {
            $user->hr->is_main_hr = true;
            $user->hr->save();
        }


        $user->notify(new \App\Notifications\AccountApproved());
        return redirect()->route('admin.manage_users')->with('flash.banner', 'User approved successfully.');
    }

    public function disapprove(User $user)
    {
        $user->is_approved = false;
        $user->save();

        return redirect()->back()->with('success', 'User disapproved successfully.');
    }

    public function delete(Request $request, User $user)
    {

        // Gate::authorize('delete', $job);

        $user->delete();

        // $user_id = $request->user()->id;

        return redirect()->route('admin.manage_users')->with('flash.banner', 'User archived successfully.');
    }

    public function hold(User $user)
    {
        $user->status = 'on_hold';
        $user->save();

        return redirect()->back()->with('flash.banner', 'User set to On Hold.');
    }

    public function unhold(User $user)
    {
        $user->status = 'active';
        $user->save();

        return redirect()->back()->with('flash.banner', 'User removed from On Hold.');
    }

    public function restore($user)
    {
        $user = User::withTrashed()->findOrFail($user);

        $user->restore();

        return redirect()->route('admin.manage_users')->with('flash.banner', 'User restored successfully.');
    }

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
                'email' => 'nullable|email|unique:users,email',
                'company_name' => 'required|string|max:255',
                'company_mobile_phone' => 'nullable|string|max:20',
                'company_tel_phone' => 'nullable|string|max:20',
                'company_email' => 'nullable|email|max:255',
                'company_street_address' => 'nullable|string|max:255',
                'company_brgy' => 'nullable|string|max:255',
                'company_city' => 'nullable|string|max:255',
                'company_province' => 'nullable|string|max:255',
                'company_zip_code' => 'nullable|string|max:10',
                'company_description' => 'nullable|string|max:1000',
                'sector_id' => 'required|exists:sectors,id',
                'category_id' => 'required|exists:categories,id',
                // HR fields
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'mobile_number' => 'required|digits_between:10,15',
                'dob' => 'required|date',
                'gender' => 'required|in:Male,Female,Other',


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
        foreach ($validRows as $index => $row) {
            $password = $this->generatePassword($row['company_name']);
            $firstName = strtolower(preg_replace('/ [^a-z]/', '', $row['first_name']));
            $lastName = strtolower(preg_replace('/[^a-z]/', '', $row['last_name']));
            $companyNameRaw = $row['company_name'] ?? '';
            preg_match_all('/[a-zA-Z]+/', $companyNameRaw, $matches);
            $companyName = strtolower(implode('', $matches[0]) ?: 'company');
            $rowIndex = $index + 1; // To avoid zero index in email

            $companyEmail = !empty($row['email'])
                ? $row['email']
                : ($firstName . '.' . $lastName . $rowIndex . '@' . $companyName . '.com');


            $user = User::create([
                'email' => $companyEmail,
                'password' => Hash::make($password),
                'role' => 'company',
                'is_approved' => true,
            ]);

            $company = Company::create([
                'user_id' => $user->id,
                'company_name' => $row['company_name'],
                'company_mobile_phone' => $row['company_mobile_phone'] ?? null,
                'company_tel_phone' => $row['company_tel_phone'] ?? null,
                'company_email' => $row['company_email'] ?? null,
                'company_street_address' => $row['company_street_address'] ?? null,
                'company_brgy' => $row['company_brgy'] ?? null,
                'company_city' => $row['company_city'] ?? null,
                'company_province' => $row['company_province'] ?? null,
                'company_zip_code' => $row['company_zip_code'] ?? null,
                'description' => $row['company_description'] ?? null,
                'sector_id' => $row['sector_id'],
                'category_id' => $row['category_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            HumanResource::create([
                'user_id' => $user->id,
                'first_name' => $row['first_name'],
                'middle_name' => $row['middle_name'] ?? null,
                'last_name' => $row['last_name'],
                'mobile_number' => $row['mobile_number'],
                'dob' => $row['dob'],
                'gender' => $row['gender'],
                'company_id' => $company->id,
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


    public function downloadVerification($institutionId)
    {
        $institution = \App\Models\Institution::findOrFail($institutionId);

        if (!$institution->verification_file_path) {
            abort(404, 'Verification file not found.');
        }

        // Use public disk if file is in storage/app/public/verification-documents
        $filePath = 'public/verification-documents/' . basename($institution->verification_file_path);

        if (!\Storage::disk('public')->exists('verification-documents/' . basename($institution->verification_file_path))) {
            abort(404, 'Verification file not found.');
        }

        return \Storage::disk('public')->download('verification-documents/' . basename($institution->verification_file_path));
    }

    public function downloadCompanyVerification($companyId)
    {
        $company = \App\Models\Company::findOrFail($companyId);

        if (!$company->verification_file_path) {
            abort(404, 'Verification file not found.');
        }

        $filePath = $company->verification_file_path; // e.g. verification-documents/filename.pdf

        if (!\Storage::disk('public')->exists($filePath)) {
            abort(404, 'Verification file not found.');
        }

        return \Storage::disk('public')->download($filePath);
    }
}
