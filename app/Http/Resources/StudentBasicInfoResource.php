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
            'name' => optional($this->user)->name,
            'phone_number' => optional($this->user)->phone_number,
            'level' => $this->level ?? null,
            'enrollmentYear' => $this->enrollment_year ?? null,
        ];
    }
}
