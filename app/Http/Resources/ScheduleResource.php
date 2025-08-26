<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day' => ucfirst($this->day), // sunday → Sunday
            'period' => $this->period,
            'start_time' => $this->start_time ? date('H:i', strtotime($this->start_time)) : null,
            'end_time' => $this->end_time ? date('H:i', strtotime($this->end_time)) : null,

            'class_subject_teacher_id' => $this->class_subject_teacher_id,

            'class_subject_teacher' => [
                'classroom' => new ClassroomBasicResource(
                    $this->whenLoaded('classSubjectTeacher', fn() => $this->classSubjectTeacher->classroom)
                ),
                'subject' => new SubjectResource(
                    $this->whenLoaded('classSubjectTeacher', fn() => $this->classSubjectTeacher->subject)
                ),
                'teacher' => new TeacherBasicResource(
                    $this->whenLoaded(
                        'classSubjectTeacher',
                        fn() =>
                        optional($this->classSubjectTeacher->teacher)->user
                    )
                ),
            ],

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
