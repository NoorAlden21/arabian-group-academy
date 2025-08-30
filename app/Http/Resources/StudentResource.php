<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->user;
        return [
            'profile_id'     => $this->id,
            'user_id'        => $this->user_id,
            'name'           => $user?->name,
            'phone_number'   => $user?->phone_number,
            'gender'         => $user?->gender,
            'level'          => $this->level,
            'enrollmentYear' => $this->enrollment_year,
            'isAssigned'     => filled($this->classroom_id),
        ];
    }
}
