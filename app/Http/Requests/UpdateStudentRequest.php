<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'phone_number' => ['sometimes', 'string', 'min:10', 'max:10', "unique:users,phone_number,{$this->id}"],
            'gender' => ['nullable', 'in:male,female'],
            'birth_date' => ['nullable', 'date'],
            'level' => ['sometimes', 'string'],
            'enrollment_year' => ['sometimes', 'string'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],

            //for the parent
            'parent_name' => ['nullable', 'string'],
            'parent_phone_number' => ['nullable', 'string', 'min:10', 'max:10'],
        ];
    }
}
