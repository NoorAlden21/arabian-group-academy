<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $classSubjectTeacher = $this->classSubjectTeacher;

        return [
            'id' => $this->id,
            'day' => $this->day,
            'period' => $this->period,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,

            'classroom' => $classSubjectTeacher && $classSubjectTeacher->classroom
                ? new ClassroomBasicResource($classSubjectTeacher->classroom)
                : null,

            'subject' => $classSubjectTeacher && $classSubjectTeacher->subject
                ? new SubjectBasicResource($classSubjectTeacher->subject)
                : null,

            'teacher' => $classSubjectTeacher
                && $classSubjectTeacher->teacher
                && $classSubjectTeacher->teacher->user
                ? new TeacherBasicResource($classSubjectTeacher->teacher->user)
                : null,
        ];
    }
}
