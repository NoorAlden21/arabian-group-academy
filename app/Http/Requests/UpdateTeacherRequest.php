<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
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
            'name' => 'sometimes|nullable|string|max:255',
            'phone_number' => 'sometimes|nullable|string|unique:users,phone_number,' . $this->route('id'),
            'gender' => 'sometimes|nullable|in:male,female',
            'birth_date' => 'sometimes|nullable|date',
            'department' => 'sometimes|nullabe|in:arabic,english,math,physics,chemistry,biology,french,history,geography,philosophy,religion',
        ];
    }
}
