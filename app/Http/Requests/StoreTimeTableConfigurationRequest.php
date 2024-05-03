<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimeTableConfigurationRequest extends FormRequest
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
            'assembly_start_time' => 'required|string',
            'assembly_end_time' => 'required|string',
            'lecture_start_time' => 'required|string',
            'lecture_end_time' => 'required|string',
            'configure_break_time' => 'required|boolean',
            'breaks' => 'required_if:configure_break_time,1|array',
            'configure_lesson' => 'required|boolean',
            'lesson_details' => 'required_if:configure_lesson,1',
        ];
    }
}
