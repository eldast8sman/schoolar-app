<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'email' => 'required|string|email|unique:users,email',
            'school_name' => 'required|string',
            'school_type' => 'required|string',
            'country' => 'required|string',
            'state' => 'required|string',
            'address' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'location_type' => 'required|string',
            'load_default' => 'required|boolean'
        ];
    }
}
