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
        // يدعم route('subject') (model binding) أو route('id') (رقمي)
        $subjectId = $this->route('subject')?->id ?? $this->route('id');

        return [
            'name' => [
                'sometimes', 'required', 'string', 'max:255',
                Rule::unique('subjects', 'name')
                    ->ignore($subjectId)            // تجاهل السجل الحالي
                // ->whereNull('deleted_at')     // فعّلها إذا عندك soft deletes
            ],

            'class_type_ids'   => ['sometimes', 'nullable', 'array'],
            'class_type_ids.*' => ['integer', 'exists:class_types,id'],
        ];
    }
}
