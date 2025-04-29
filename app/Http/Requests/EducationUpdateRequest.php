<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EducationUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true; // You can add your authorization logic here
    }

    public function rules()
    {
        return [
            'education' => 'required|array',
            'education.id' => 'nullable|exists:educations,id',
            'education.graduate_education_institution_id' => 'required|exists:institutions,id', // Updated field name
            'education.graduate_education_program' => 'required|string|max:255', // Updated field name
            'education.graduate_education_field_of_study' => 'required|string|max:255', // Updated field name
            'education.graduate_education_start_date' => 'required|date', // Updated field name
            'education.graduate_education_end_date' => 'nullable|date|after:education.graduate_education_start_date', // Updated field name
            'education.graduate_education_description' => 'nullable|string|max:1000', // Updated field name
        ];
    }
}