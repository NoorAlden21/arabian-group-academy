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
            'day' => $this->day,
            'period' => $this->period,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'class_subject_teacher_id' => $this->class_subject_teacher_id,
            'class_subject_teacher' => [
                'classroom' => new ClassroomBasicResource($this->whenLoaded('classSubjectTeacher', function() {
                    return $this->classSubjectTeacher->classroom;
                })),
                'subject' => new SubjectResource($this->whenLoaded('classSubjectTeacher', function() {
                    return $this->classSubjectTeacher->subject;
                })),
                'teacher' => new TeacherBasicResource($this->whenLoaded('classSubjectTeacher', function() {
                    return optional($this->classSubjectTeacher->teacher)->user;
                })),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
