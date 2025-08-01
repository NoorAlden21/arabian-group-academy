<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin') || $this->user()->hasRole('super_admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('subjects')->ignore($this->route('id'))],
        ];
    }
}
