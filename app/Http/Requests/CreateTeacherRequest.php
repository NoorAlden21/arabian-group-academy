<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTeacherRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:users,phone_number',
            'password' => 'required|string|min:6',
            'gender' => 'required|in:male,female',
            'birth_date' => 'required|date',
            'department' => 'required|in:arabic,english,math,physics,chemistry,biology,french,history,geography,philosophy,religion',
        ];
    }
}
