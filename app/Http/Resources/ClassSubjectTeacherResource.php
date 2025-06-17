<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassSubjectTeacherResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'classroomId' => $this->classroom_id, // CamelCase
            'subjectId' => $this->subject_id,     // CamelCase
            'teacherId' => $this->teacher_id,     // CamelCase
            'createdAt' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updatedAt' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,

            'classroom' => ClassroomBasicResource::make($this->whenLoaded('classroom')),
            'subject' => SubjectBasicResource::make($this->whenLoaded('subject')),
            'teacher' => TeacherBasicResource::make($this->whenLoaded('teacher')),
        ];
    }
}
