<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpsertExamsRequest extends FormRequest
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
            'class_type_id'         => ['required','integer','exists:class_types,id'],
            'exams'                 => ['required','array','min:1'],
            'exams.*.subject_id'    => ['required','integer','exists:subjects,id'],
            'exams.*.scheduled_at'  => ['required','date'],
            'exams.*.duration_minutes' => ['required','integer','min:10','max:600'],
            'exams.*.room'          => ['nullable','string','max:100'],
            'exams.*.max_score'     => ['nullable','integer','min:1','max:1000'],
            'notes'                 => ['nullable','string','max:2000'],
        ];
    }
}
