<?php

namespace App\Http\Resources\Mobile;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name, //
            'phone_number' => $this->phone_number, //
            'gender' => $this->gender, //
            'mother_name' => $this->mother_name, //
            'birth_date' => $this->birth_date ? $this->birth_date->format('Y-m-d') : null, //
            'roles' => $this->getRoleNames(),
            // 'photos' => PhotoResource::collection($this->whenLoaded('photos')), //
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($this->isStudent() && $this->relationLoaded('studentProfile')) {
            $data['student_profile'] = StudentProfileResource::make($this->studentProfile);
        }

        if ($this->isTeacher() && $this->relationLoaded('teacherProfile')) {
            $data['teacher_profile'] = TeacherProfileResource::make($this->teacherProfile);
        }

        if ($this->isParent() && $this->relationLoaded('parentProfile')) {
            $data['parent_profile'] = ParentProfileResource::make($this->parentProfile);
        }

        return $data;
    }
}
