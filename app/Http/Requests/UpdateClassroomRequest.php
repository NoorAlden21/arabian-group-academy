<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdateClassroomRequest extends FormRequest
{

    public function authorize(): bool
    {

        return true;
    }


    public function rules(): array
    {

        $classroomId = $this->route('classroom');

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('classrooms', 'name')->ignore($classroomId)],
            'level' => ['sometimes', 'string', 'max:255'],
            'year' => ['sometimes', 'string', 'max:4'],
            'students_count' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }


    public function messages(): array
    {
        return [

        ];
    }
}
