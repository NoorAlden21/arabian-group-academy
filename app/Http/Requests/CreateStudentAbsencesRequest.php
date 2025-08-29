<?php

namespace App\Http\Requests;

use App\Models\StudentProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateStudentAbsencesRequest extends FormRequest
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
            'classroom_id' => ['required','integer','exists:classrooms,id'],
            'date'         => ['required','date_format:Y-m-d'],
            'period'       => ['required','integer','min:1'],
            'entries'      => ['required','array'],
            'entries.*.student_profile_id' => ['required','integer','exists:student_profiles,id'],
            'entries.*.status'             => ['required','in:absent,late'],
        ];
    }
}
