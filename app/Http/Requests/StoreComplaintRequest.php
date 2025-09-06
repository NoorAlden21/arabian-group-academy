<?php

namespace App\Http\Requests;

use App\Models\Complaint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComplaintRequest extends FormRequest
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
            // نوع الهدف 'student' أو 'teacher' وليس اسم الكلاس
            'target_type' => ['required', Rule::in(['student', 'teacher'])],

            // رقم بروفايل الهدف (student_profiles.id أو teacher_profiles.id)
            'target_id'   => ['required', 'integer'],

            'topic'       => ['required', 'string', Rule::in(Complaint::TOPICS)],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $type = $this->input('target_type');
            $id   = (int) $this->input('target_id');

            $class = Complaint::classFromLabel($type);
            if (!$class) {
                $v->errors()->add('target_type', 'نوع الهدف غير صالح.');
                return;
            }

            $exists = $class::query()->whereKey($id)->exists();
            if (!$exists) {
                $v->errors()->add('target_id', 'الهدف غير موجود.');
            }
        });
    }
}
