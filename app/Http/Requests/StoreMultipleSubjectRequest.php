<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMultipleSubjectRequest extends FormRequest
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
            'subjects' => 'required|array',
            'subjects.*.name' => 'required|string',
            'subjects.*.compulsory' => 'required|boolean',
            'subjects.*.primary_teacher' => 'integer|exists:school_teachers,id|nullable',
            'subjects.*.support_teacher' => 'integer|exists:school_teachers,id|nullable'
        ];
    }
}
