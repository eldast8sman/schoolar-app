<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolParentRequest extends FormRequest
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
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'mobile' => 'required|string',
            'email' => 'string|email|nullable',
            'nationality' => 'required|string',
            'occupation' => 'string|nullable',
            'address' => 'required|string',
            'town' => 'required|string',
            'lga' => 'required|string',
            'state' => 'required|string',
            'country' => 'string|nullable',
            'file' => 'file|mimes:png,jpg,jpeg,gif|max:500|nullable'
        ];
    }
}
