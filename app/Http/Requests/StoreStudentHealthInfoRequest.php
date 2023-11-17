<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentHealthInfoRequest extends FormRequest
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
            'weight' => 'numeric|nullable',
            'weight_measurement' => 'string|nullable',
            'height' => 'numeric|nullable',
            'height_measurement' => 'string|nullable',
            'blood_group' => 'string|nullable',
            'genotype' => 'string|nullable',
            'immunizations' => 'array|nullable',
            'disabled' => 'boolean|nullable',
            'disability' => 'array|nullable'
        ];
    }
}
