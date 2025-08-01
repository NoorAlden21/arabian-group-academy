<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignTeachersRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assignments' => 'required|array|min:1',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
            'assignments.*.teacher_profile_id' => 'required|exists:teacher_profiles,id',
        ];
    }
}
