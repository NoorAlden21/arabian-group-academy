<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentBasicInfoResource extends JsonResource
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
            'phoneNumber' => $this->phone_number,
            'level' => $this->studentProfile->level ?? null,
            'enrollmentYear' => $this->studentProfile->enrollment_year ?? null,
        ];
    }
}
