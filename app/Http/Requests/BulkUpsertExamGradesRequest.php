<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpsertExamGradesRequest extends FormRequest
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
            'class_id'       => ['nullable','integer','exists:classrooms,id'],
            'records'        => ['required','array','min:1'],
            'records.*.student_profile_id' => ['required','integer','exists:student_profiles,id'],
            'records.*.status' => ['required','in:present,absent,excused,cheated,incomplete'],
            'records.*.score'  => ['nullable','numeric','min:0'],
            'records.*.remark' => ['nullable','string','max:255'],
        ];
    }
}
