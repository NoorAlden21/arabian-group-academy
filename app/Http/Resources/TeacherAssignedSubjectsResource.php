<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherAssignedSubjectsResource extends JsonResource
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
            'classroom' => [
                'id' => $this->classroom->id ?? null,
                'name' => $this->classroom->name ?? null,
                'year' => $this->classroom->year ?? null,
            ],
            'subject' => [
                'id' => $this->subject->id ?? null,
                'name' => $this->subject->name ?? null,
            ],
        ];
    }
}
