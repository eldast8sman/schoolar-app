<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddLocationToSchoolRequest extends FormRequest
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
            'locations' => 'required|array',
            'locations.*.address' => 'required|string',
            'locations.*.location_type' => 'required|string',
            'locations.*.syllabus' => 'string|nullable',
            'locations.*.address' => 'required|string',
            'locations.*.town' => 'required|string',
            'locations.*.state'=> 'required|string',
            'locations.*.location_type' => 'required|string',
            'locations.*.load_default' => 'required|boolean'
        ];
    }
}
