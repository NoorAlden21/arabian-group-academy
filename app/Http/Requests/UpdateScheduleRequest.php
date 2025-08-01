<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin') || $this->user()->hasRole('super_admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'class_subject_teacher_id' => ['sometimes', 'required', 'exists:class_subject_teachers,id'],
            'day' => ['sometimes', 'required', 'string', Rule::in(['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'])],
            'period' => ['sometimes', 'required', 'integer', 'min:1', 'max:10'],
            'start_time' => ['sometimes', 'required', 'date_format:H:i:s'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i:s', 'after:start_time'],
        ];
    }
}
