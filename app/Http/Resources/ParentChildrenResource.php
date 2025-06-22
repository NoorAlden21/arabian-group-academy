<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentChildrenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->user->name,
            'phone_number' => $this->user->phone_number,
            'level' => $this->level,
            'enrollment_year' => $this->enrollment_year,
            'classroom' => optional($this?->classroom)->name
        ];
    }
}
