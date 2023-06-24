<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportClassesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'school_location_id' => 'required|integer|exists:school_locations,id'
        ];
    }
}
