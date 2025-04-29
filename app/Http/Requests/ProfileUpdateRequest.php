<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true; // You can add your authorization logic here
    }

    public function rules()
    {
        return [
            'graduate_full_name' => 'required|string|max:255',
            'graduate_professional_title' => 'required|string|max:255',
            'graduate_email' => 'required|email|unique:users,email',
            'graduate_phone' => 'nullable|string|max:20',
            'graduate_location' => 'nullable|string|max:255',
            'graduate_birthdate' => 'nullable|date',
            'graduate_gender' => 'nullable|string|max:50',
            'graduate_ethnicity' => 'nullable|string|max:100',
            'graduate_address' => 'nullable|string|max:255',
            'graduate_about_me' => 'nullable|string|max :1000',
            'graduate_picture_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}