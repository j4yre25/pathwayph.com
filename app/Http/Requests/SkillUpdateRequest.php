<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SkillUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true; // You can add your authorization logic here
    }

    public function rules()
    {
        return [
            'skills' => 'required|array',
            'skills.*.graduate_skills_name' => 'required|string|max:255', // Updated field name
            'skills.*.graduate_skills_proficiency' => 'required|integer|min:1|max:100', // Updated field name
            'skills.*.graduate_skills_type' => 'required|string|in:technical,soft,language,tool', // Updated field name
            'skills.*.graduate_skills_years_experience' => 'required|numeric|min:0', // Updated field name
        ];
    }
}