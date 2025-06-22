<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentFullInfoResource extends JsonResource
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
            'occupation' => $this?->occupation,
            
            'children' => ParentChildrenResource::collection($this->children),
        ];
    }
}
