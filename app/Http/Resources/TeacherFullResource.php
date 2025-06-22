<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherFullResource extends JsonResource
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
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date,
            'department' => $this->teacherProfile->department ?? null,

            'teachable_subjects' => $this->teacherProfile?->teachableSubjects?->map(function ($item){
                return[
                    'class_type' => $item->classTypeSubject->classType->name,
                    'subject' => $item->classTypeSubject->subject->name
                ];
            }) ?? [],
        ];
    }
}
