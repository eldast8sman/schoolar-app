<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolTeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email',
            'mobile' => 'required|string',
            'file' => 'file|mimes:jpg,jpeg,png|nullable|max:500',
            'certifications.*.name' => 'string|nullable',
            'certifications.*.file' => 'file|mimes:jpg,jpeg,png|max:500'
        ];
    }
}
