<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'min:8'],
            'role' => ['nullable', 'string', 'max:255'],
            'peso_first_name' => ['nullable', 'string', 'max:255'],
            'peso_last_name' => ['nullable', 'string', 'max:255'],
            'graduate_first_name' => ['nullable', 'string', 'max:255'],            
            'graduate_middle_initial' => ['nullable', 'string', 'max:1'],
            'graduate_last_name' => ['nullable', 'string', 'max:255'],
            'graduate_school_graduated_from' => ['nullable', 'string', 'max:255'],
            'graduate_program_completed' => ['nullable', 'string', 'max:255'],
            'graduate_year_graduated' => ['nullable', 'date'],
            'graduate_skills' => ['nullable', 'array'],
            'graduate_skills.*' => ['string', 'max:255'],
            'personal_summary' => ['nullable', 'string', 'max:255'],
            'graduate_professional_title' => 'nullable|string|max:255',
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_sector' => ['nullable', 'string', 'max:255'],
            'company_category' => ['nullable', 'string', 'max:255'],
            'company_hr_last_name' => ['nullable', 'string', 'max:255'],
            'company_hr_first_name' => ['nullable', 'string', 'max:255'],
            'company_hr_middle_initial' => ['nullable', 'string', 'max:10'],
            'institution_type' => ['nullable', 'string', 'max:255'],
            'institution_address' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'institution_president_last_name' => ['nullable', 'string', 'max:255'],
            'institution_president_first_name' => ['nullable', 'string', 'max:255'],
            'institution_career_officer_first_name' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email && $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'email' => $input['email'],
                'role' => $input['role'] ?? $user->role,
                'peso_first_name' => $input['peso_first_name'] ?? $user->peso_first_name,
                'peso_last_name' => $input['peso_last_name'] ?? $user->peso_last_name,
                'graduate_first_name' => $input['graduate_first_name'] ?? $user->graduate_first_name,
                'graduate_middle_initial' => $input['graduate_middle_initial'] ?? $user->graduate_middle_initial,
                'graduate_last_name' => $input['graduate_last_name'] ?? $user->graduate_last_name,
                'graduate_school_graduated_from' => $input['graduate_school_graduated_from'] ?? $user->graduate_school_graduated_from,
                'graduate_program_completed' => $input['graduate_program_completed'] ?? $user->graduate_program_completed,
                'graduate_year_graduated' => $input['graduate_year_graduated'] ?? $user->graduate_year_graduated,
                'graduate_skills' => $input['graduate_skills'] ?? $user->graduate_skills,                
                'graduate_professional_title' => $input['graduate_professional_title'] ?? $user->graduate_professional_title,
                'personal_summary' => $input['personal_summary'] ?? $user->personal_summary,
                'company_name' => $input['company_name'] ?? $user->company_name,
                'company_address' => $input['company_address'] ?? $user->company_address,
                'company_sector' => $input['company_sector'] ?? $user->company_sector,
                'company_category' => $input['company_category'] ?? $user->company_category,
                'contact_number' => $input['contact_number'] ?? $user->contact_number,
                'company_hr_last_name' => $input['company_hr_last_name'] ?? $user->company_hr_last_name,
                'company_hr_first_name' => $input['company_hr_first_name'] ?? $user->company_hr_first_name,
                'company_hr_middle_initial' => $input['company_hr_middle_initial'] ?? $user->company_hr_middle_initial,
                'institution_type' => $input['institution_type'] ?? $user->institution_type,
                'institution_address' => $input['institution_address'] ?? $user->institution_address,
                'institution_president_last_name' => $input['institution_president_last_name'] ?? $user->institution_president_last_name,
                'institution_president_first_name' => $input['institution_president_first_name'] ?? $user->institution_president_first_name,
                'institution_career_officer_first_name' => $input['institution_career_officer_first_name'] ?? $user->institution_career_officer_first_name,
            ])->save();
        }
    }


    
    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->update([
            'email' => $input['email'],
        ]);

        $user->sendEmailVerificationNotification();
    }
}
