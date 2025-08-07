<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateHomeworkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('teacher');
    }

    public function rules(): array
    {
        $teacherProfileId = $this->user()->teacherProfile->id;
        return [
            'class_subject_teacher_id' => [
                'required',
                'exists:class_subject_teachers,id',
                Rule::exists('class_subject_teachers', 'id')->where(function ($query) use ($teacherProfileId) {
                    $query->where('teacher_profile_id', $teacherProfileId);
                }),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_time' => ['nullable', 'date_format:Y-m-d H:i:s', 'after_or_equal:now'],
        ];
    }
}
