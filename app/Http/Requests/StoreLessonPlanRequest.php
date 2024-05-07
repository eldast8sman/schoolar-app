<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonPlanRequest extends FormRequest
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
            'main_class_id' => 'required|integer',
            'sub_class_id' => 'required|integer',
            'session_id' => 'required|integer',
            'term_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'teacher_id' => 'required|integer',
            'lesson_plan' => 'required|string',
            'file' => 'file|mimes:pdf,doc,docx,txt,xls,xlsx,wps|nullable',
        ];
    }
}
