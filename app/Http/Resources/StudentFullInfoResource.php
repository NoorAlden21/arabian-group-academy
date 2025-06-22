<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentFullInfoResource extends JsonResource
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
            'birthDate' => $this->birth_date,
            //'created_at' => $this->created_at,

            'profile' => [
                'level' => $this->studentProfile->level ?? null,
                'enrollmentYear' => $this->studentProfile->enrollment_year ?? null,
                'classroom' => $this->studentProfile->classroom->name ?? null,
            ],

            'parent' => [
                'id' => optional($this->studentProfile->parent)->id,
                'name' => optional($this->studentProfile->parent)->name,
                'phone_number' => optional($this->studentProfile->parent)->phone_number,
            ],
        ];
    }
}
