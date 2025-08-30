<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamTermRequest extends FormRequest
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
            'name'          => ['sometimes','string','max:255'],
            'academic_year' => ['sometimes','string','max:9'],
            'term'          => ['sometimes','in:midterm,final,other'],
            'start_date'    => ['sometimes','nullable','date'],
            'end_date'      => ['sometimes','nullable','date','after_or_equal:start_date'],
            'status'        => ['sometimes','in:draft,published,archived'],
        ];
    }
}
