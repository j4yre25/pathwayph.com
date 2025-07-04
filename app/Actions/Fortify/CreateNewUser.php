<?php

namespace App\Actions\Fortify;

use App\Models\Institution;
use App\Models\User;
use App\Models\Company;
use App\Models\HumanResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Carbon\Carbon;
use App\Actions\Fortify\PasswordValidationRules;
use App\Models\Graduate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */


    public function create(array $input): User
    {
        // Force company_not_found to boolean for reliable validation
        if (isset($input['company_not_found'])) {
            $input['company_not_found'] = filter_var($input['company_not_found'], FILTER_VALIDATE_BOOLEAN);
        }

        $role = $this->determineRole(request());

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'mobile_number' => ['required', 'digits_between:10,15', 'regex:/^9\d{9}$/'],
            'password' => $this->passwordRules(),
            'telephone_number' => ['nullable', 'regex:/^(02\d{7}|0\d{2}\d{7})$/'],
        ];

        // Add role-specific validation rules
        switch ($role) {
            case 'graduate':
                $rules['graduate_school_graduated_from'] = ['required', 'integer', 'exists:users,id'];
                $rules['graduate_program_completed'] = ['required', 'exists:programs,name'];
                $rules['employment_status'] = ['required', 'in:Employed,Underemployed,Unemployed'];
                $rules['current_job_title'] = ['required_if:employment_status,Employed,Underemployed', 'nullable', 'string', 'max:255'];
                $rules['graduate_year_graduated'] = ['required', 'exists:school_years,school_year_range'];
                $rules['graduate_degree'] = ['required', 'exists:degrees,id'];

                if (!empty($input['company_not_found'])) {
                    // Company not found: require other_company_name and sector, but company_name is nullable
                    $rules['other_company_name'] = ['required', 'string', 'max:255'];
                    $rules['other_company_sector'] = ['required', 'exists:sectors,id'];
                    $rules['company_name'] = ['nullable', 'string', 'max:255'];
                } else {
                    // Existing company: require company_name, other_company_name/sector are nullable
                    $rules['company_name'] = ['required', 'string', 'max:255'];
                    $rules['other_company_name'] = ['nullable', 'string', 'max:255'];
                    $rules['other_company_sector'] = ['nullable', 'exists:sectors,id'];
                }
                break;
            case 'company':
                // $rules['company_name'] = ['required', 'string', 'max:255'];
                // $rules['company_street_address'] = ['required', 'string', 'max:255'];
                // $rules['company_brgy'] = ['required', 'string', 'max:255'];
                // $rules['company_city' ] = ['required', 'string', 'max:255'];
                // $rules['company_province' ] = ['required', 'string', 'max:255'];
                // $rules['company_zip_code' ] = ['required', 'string', 'max:4'];
                // $rules['company_email' ] = ['required', 'string', 'email', 'max:255'];
                // $rules['company_mobile_phone' ] = ['required', 'numeric', 'digits_between:10,15', 'regex:/^9\d{9}$/'];
                // $rules['category' ] = 'required|exists:categories,id';
                break;
            case 'institution':
                $rules['institution_type' ] = ['required', 'string'];
                $rules['institution_name' ] = ['required', 'string'];
                $rules['institution_address' ] = ['required', 'string'];
                $rules['institution_president_last_name' ] = ['required', 'string', 'max:255'];
                $rules['institution_president_first_name' ] = ['required', 'string', 'max:255'];
                $rules['first_name' ] = ['nullable', 'string', 'max:255'];
                $rules['last_name' ] = ['nullable', 'string', 'max:255'];

                break;
            default:
                break;
        }

        // Custom validation messages
        $messages = [
            'first_name.required' => 'The first name field is required.',
            'last_name.required' => 'The last name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.required' => 'The password field is required.',
            'dob.required' => 'The date of birth field is required.',
            'dob.date' => 'The date of birth must be a valid date.',
            'dob.before_or_equal' => 'You must be at least 18 years old to register.',
            'gender.required' => 'The gender field is required.',
            'gender.in' => 'The selected gender is invalid.',
            'mobile_number.required' => 'The contact number field is required.',
            'mobile_number.digits_between' => 'The contact number must be in valid format (e.g., +63 912 345 6789).',
            'mobile_number.regex' => 'The contact number must be in valid format (e.g., +63 912 345 6789).',
            'telephone_number.regex' => 'The telephone number must be in valid format (e.g., (02) 123 4567 or (032) 123 1234).',
            'graduate_first_name.required' => 'The first name field is required.',
            'graduate_last_name.required' => 'The last name field is required.',
            'graduate_middle_initial.required' => 'The middle initial field is required.',
            'graduate_school_graduated_from.required' => 'The school graduated from field is required.',
            'graduate_program_completed.required' => 'The program completed field is required.',
            'graduate_year_graduated.required' => 'The school year field is required.',
            'company_name.required' => 'The company name field is required.',
            'company_street_address.required' => 'The street address field is required.',
            'company_brgy.required' => 'The barangay field is required.',
            'company_city.required' => 'The city field is required.',
            'company_province.required' => 'The province field is required.',
            'company_zip_code.required' => 'The zip code field is required.',
            'company_email.required' => 'The company email field is required.',
            'company_email.email' => 'The company email must be a valid email address.',
            'company_mobile_phone.required' => 'The contact number field is required.',
            'company_mobile_phone.regex' => 'The contact number must be in valid format (e.g., +63 912 345 6789).',
            'institution_type.required' => 'The institution type field is required.',
            'institution_name.required' => 'The institution name field is required.',
            'institution_address.required' => 'The institution address field is required.',
            'institution_president_last_name.required' => 'The president last name field is required.',
            'institution_president_first_name.required' => 'The president first name field is required.',
            'institution_career_officer_first_name.required' => 'The career officer first name field is required.',
            'institution_career_officer_last_name.required' => 'The career officer last name field is required.',
            'employment_status.required' => 'The employment status field is required.',
            'employment_status.in' => 'The selected employment status is invalid.',
            'current_job_title.required_if' => 'The current job title field is required when employed or underemployed.',
        ];


        Validator::make($input, $rules, $messages)->validate();

        $userData = [
            'first_name' => $input['first_name'],
            'middle_name' => $input['middle_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'role' => $role,
            'mobile_number' => $input['mobile_number'],
            'is_approved' => false, // <-- Ensure this is set
        ];

        $user = User::create($userData);

        // Store in Institutions table
        if ($role === 'institution') {
            DB::table('institutions')->insert([
                'user_id' => $user->id,
                'institution_name' => $input['institution_name'],
                'institution_type' => $input['institution_type'],
                'institution_address' => $input['institution_address'],
                'address' => $input['institution_address'],
                'email' => $input['email'],
                'website' => $input['website'] ?? null,
                'contact_number' => $input['mobile_number'],
                'institution_president_first_name' => $input['institution_president_first_name'],
                'institution_president_last_name' => $input['institution_president_last_name'],
                'telephone_number' => $input['telephone_number'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $user->assignRole($role);
            return $user; // <-- Ensure return here
        }

        // Store in Companies table
        if ($role === 'company') {
            // // $category = \App\Models\Category::find($input['category']);
            // // $company = Company::create([
            // //     'user_id' => $user->id,
            // //     'company_name' => $input['company_name'],
            // //     'company_street_address' => $input['company_street_address'],
            // //     'company_brgy' => $input['company_brgy'],
            // //     'company_city' => $input['company_city'],
            // //     'sector_id' => $category ? $category->sector_id : null,
            // //     'category_id' => $category->id,
            // //     'company_province' => $input['company_province'],
            // //     'company_zip_code' => $input['company_zip_code'],
            // //     'company_email' => $input['company_email'],
            // //     'company_mobile_phone' => $input['company_mobile_phone'],
            // //     'company_tel_phone' => $input['telephone_number'],
            // //     'created_at' => now(),
            // //     'updated_at' => now(),
            // // ]);
            $user->hr()->create([
                'first_name' => $input['first_name'],
                'middle_name' => $input['middle_name'],
                'last_name' => $input['last_name'],
                'mobile_number' => $input['mobile_number'],
                'dob' => $input['dob'],
                'gender' => $input['gender'],
                // 'company_id' => $company->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $user->assignRole($role);
            return $user; // <-- Ensure return here
        }

        // Store in Graduates table
        if ($role === 'graduate') {
            $company_id = null;
            $not_company_id = null;
            $sector_id = null;

            if (!empty($input['company_not_found'])) {
                // Always create a not_company record if company_not_found is checked
                $notCompany = \App\Models\NotCompany::create([
                    'name' => $input['other_company_name'],
                    'sector_id' => $input['other_company_sector'] ?? null,
                ]);
                $not_company_id = $notCompany->id;
                $sector_id = $input['other_company_sector'] ?? null;
            } else {
                // Existing company selected
                $company = Company::where('company_name', $input['company_name'])->first();
                $company_id = $company ? $company->id : null;
                $sector_id = $company ? $company->sector_id : null;
            }

            DB::table('graduates')->insert([
                'user_id' => $user->id,
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'middle_name' => $input['middle_name'],
                'dob' => $input['dob'],
                'current_job_title' => ($input['employment_status'] === 'Unemployed') ? 'N/A' : $input['current_job_title'],
                'employment_status' => $input['employment_status'],
                'contact_number' => $input['mobile_number'],
                'institution_id' => $input['graduate_school_graduated_from'],
                'program_id' => $this->getProgramId($input['graduate_program_completed']),
                'school_year_id' => $this->getYearId($input['graduate_year_graduated']),
                'degree_id' => $input['graduate_degree'],
                'company_id' => $company_id,
                'not_company' => $not_company_id,
                'sector_id' => $sector_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user->assignRole($role);
            return $user;
        }

        $user->assignRole($role);
        return $user; // Fallback return for any other role
    }

    protected function determineRole(Request $request): string
    {

        if ($request->is('register/graduate')) {
            return 'graduate';
        } elseif ($request->is('register/company')) {
            return 'company';
        } elseif ($request->is('register/institution')) {
            return 'institution';
        }

        return 'user'; // Default role if none match
    }
    private function getProgramId($programName)
    {
        return DB::table('programs')->where('name', $programName)->value('id');
    }

    private function getInstitutionId($institutionName)
    {
        return DB::table('institutions')
            ->whereRaw('LOWER(TRIM(institution_name)) = ?', [strtolower(trim($institutionName))])
            ->value('id');
    }
    private function getYearId($schoolYearRange)
    {
        return DB::table('school_years')->where('school_year_range', $schoolYearRange)->value('id');
    }
}
