<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateClassroomRequest extends FormRequest
{

    public function authorize(): bool
    {

        return true;
    }


    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:classrooms,name'],
            'level' => ['required', 'string', 'max:255'],
            'year' => ['required', 'string', 'max:4'],
            'students_count' => ['nullable', 'integer', 'min:0'],
        ];
    }


    public function messages(): array
    {
        return [];
    }
}
