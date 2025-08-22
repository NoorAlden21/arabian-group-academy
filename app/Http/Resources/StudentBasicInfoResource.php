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
        $profile = $this->studentProfile;

        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'phone_number'   => $this->phone_number,
            'level'          => $profile->level ?? null,
            'enrollmentYear' => $profile->enrollment_year ?? null,
            'isAssigned'     => (bool) optional($profile)->classroom_id,
        ];
    }
}
