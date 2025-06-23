<?php

namespace App\Http\Resources\Mobile;

use App\Http\Resources\ClassroomFullResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentProfileResource extends JsonResource
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
            'level' => $this->level,
            'previous_status' => $this->previous_status,
            'gpa' => $this->gpa,
            'enrollment_year' => $this->enrollment_year,
            'classroom' => ClassroomFullResource::make($this->whenLoaded('classroom')),
            'parent' => ParentProfileResource::make($this->whenLoaded('parent')),
        ];
    }
}
