<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolStudentRequest extends FormRequest
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
            'mobile' => 'string|nullable',
            'email' => 'string|nullable',
            'registration_id' => 'required|string|unique:school_students,registration_id',
            'sub_class_id' => 'required|integer|exists:sub_classes,id',
            'dob' => 'date|nullable',
            'gender' => 'required|string',
            'file' => 'file|mimes:png,jpg,jpeg,gif|nullable'
        ];
    }
}
