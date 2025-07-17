<?php

namespace App\Http\Resources\Mobile;

use App\Http\Resources\ClassroomFullResource;
use App\Http\Resources\SubjectBasicResource;
use App\Http\Resources\TeacherBasicResource;
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
        return [
            'id' => $this->id,
            'day' => $this->day,
            'period' => $this->period,
            'start_time' => $this->start_time ? $this->start_time : null,
            'end_time' => $this->end_time ? $this->end_time : null,
              'classroom' => ClassroomFullResource::make($this->whenLoaded('classSubjectTeacher', function () {
                return $this->classSubjectTeacher->classroom; // <--- تم إزالة ->whenLoaded('classroom')
            })),
            'subject' => SubjectBasicResource::make($this->whenLoaded('classSubjectTeacher', function () {
                return $this->classSubjectTeacher->subject; // <--- تم إزالة ->whenLoaded('subject')
            })),
            'teacher' => TeacherBasicResource::make($this->whenLoaded('classSubjectTeacher', function () {
                return $this->classSubjectTeacher->teacher; // <--- تم إزالة ->whenLoaded('teacher')
            })),
        ];
    }
}
