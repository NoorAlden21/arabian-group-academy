<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHomeworkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('teacher');
    }

    public function rules(): array
    {
        return [
            'class_subject_teacher_id' => ['sometimes', 'required', 'exists:class_subject_teachers,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'due_time' => ['nullable', 'date_format:Y-m-d H:i:s', 'after_or_equal:now'],
        ];
    }
}
