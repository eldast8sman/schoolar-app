<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentTypeRequest extends FormRequest
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
            'assessment_scores' => 'required|array',
            'assessment_scores.*.assessment_type' => 'required|string',
            'assessment_scores.*.percentage' => 'required|numeric|min:1|max:100',
            'minimum_pass_score' => 'required|numeric|min:1|max:100'
        ];
    }
}
