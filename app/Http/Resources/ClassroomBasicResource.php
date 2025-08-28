<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ClassroomBasicResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'level' => $this->level,
            'year' => $this->year,
            'studentsCount' => $this->students_count,
            'type' => $this->whenLoaded('classType', function () {
                return [
                    'id' => $this->classType->id,
                    'name' => $this->classType->name,
                ];
            }),
        ];
    }
}
