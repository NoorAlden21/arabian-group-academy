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
            'id'             => $this->id, // من StudentProfile
            'name'           => optional($this->user)->name, // من User عبر علاقة user()
            'phone_number'   => optional($this->user)->phone_number, // من User عبر علاقة user()
            'level'          => $this->level, // من StudentProfile
            'enrollmentYear' => $this->enrollment_year, // من StudentProfile
            'isAssigned'     => (bool) optional($this->classroom)->name, // من StudentProfile عبر علاقة classroom()
        ];
    }
}
