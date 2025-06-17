<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ClassroomFullResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'level' => $this->level,
            'year' => $this->year,
            'studentsCount' => $this->students_count, // CamelCase
            'createdAt' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null, // تنسيق التاريخ
            'updatedAt' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null, // تنسيق التاريخ
            'deletedAt' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null, // في حالة الحذف الناعم


            'students' => StudentBasicInfoResource::collection($this->whenLoaded('students')),


            'classSubjectTeachers' => ClassSubjectTeacherResource::collection($this->whenLoaded('classSubjectTeachers')),
        ];
    }
}
