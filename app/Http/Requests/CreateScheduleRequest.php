<?php

namespace App\Http\Requests;

use App\Models\ClassSubjectTeacher;
use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;

class CreateScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin') || $this->user()->hasRole('super_admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // جلب بيانات العلاقة class_subject_teacher
        $classSubjectTeacher = ClassSubjectTeacher::find($this->input('class_subject_teacher_id'));
        $classroomId = optional($classSubjectTeacher)->classroom_id;
        $teacherProfileId = optional($classSubjectTeacher)->teacher_profile_id;
        $day = $this->input('day');

        return [
            'class_subject_teacher_id' => [
                'required',
                'exists:class_subject_teachers,id',
            ],
            'day' => [
                'required',
                'string',
                'in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            ],
            'period' => [
                'required',
                'integer',
                'min:1',
                'max:10',
                function ($attribute, $value, $fail) use ($day, $classroomId, $teacherProfileId) {
                    // تحقق من التعارض مع classroom
                    $classroomConflict = Schedule::where('day', $day)
                        ->where('period', $value)
                        ->whereHas('classSubjectTeacher', function ($q) use ($classroomId) {
                            $q->where('classroom_id', $classroomId);
                        })
                        ->exists();

                    if ($classroomConflict) {
                        $fail("This classroom is already scheduled for this period.");
                    }

                    // تحقق من التعارض مع teacher
                    $teacherConflict = Schedule::where('day', $day)
                        ->where('period', $value)
                        ->whereHas('classSubjectTeacher', function ($q) use ($teacherProfileId) {
                            $q->where('teacher_profile_id', $teacherProfileId);
                        })
                        ->exists();

                    if ($teacherConflict) {
                        $fail("This teacher already has a class in this period.");
                    }
                },
            ],
            'start_time' => ['required', 'date_format:H:i:s'],
            'end_time' => ['required', 'date_format:H:i:s', 'after:start_time'],
        ];
    }
}
