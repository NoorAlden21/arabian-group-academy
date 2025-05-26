<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class createStudentRequest extends FormRequest
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
            'parent_name' => ['required','string'],
            'parent_phone_number' => ['required','string','size:10','unique:users,phone_number'],
            'parent_password' => ['required'],
            
            
            'name' => ['required','string'],
            'phone_number' => ['required','string','size:10','unique:users,phone_number'],
            'gender' => ['required','in:male,female'],
            'birthdate' => ['required','date'],
            'password' => ['required'],

            //student profile
            'level' => ['required','string'],
            'enrollment_year' => ['required', 'string'],
            'classroom_id' => ['nullable','exists:classrooms,id'],
        ];
    }
}
// {
//   "name": "Student Name",
//   "phone_number": "0912345678",
//   "password": "secret123",
//   "password_confirmation": "secret123",
//   "level": "9",
//   "enrollment_year": "2025",
//   "classroom_id": null,

//   "parent_name": "Parent Name",
//   "parent_phone_number": "0998765432",
//   "parent_password": "parent123",
//   "parent_password_confirmation": "parent123"
// }


