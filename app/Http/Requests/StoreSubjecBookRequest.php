<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjecBookRequest extends FormRequest
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
            'book_name' => 'required|string',
            'authors' => 'required|string',
            'year_published' => 'required|integer',
            'compulsory' => 'required|boolean',//1or0
            'can_purchase_externally' => 'required|boolean', //1or0
            'cost' => 'required|numeric',
            'file' => 'file|mimes:png,jpg,jpeg,gif|nullable'
        ];
    }
}
