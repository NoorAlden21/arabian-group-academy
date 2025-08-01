<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin') || $this->user()->hasRole('super_admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:subjects,name'],
        ];
    }
}
