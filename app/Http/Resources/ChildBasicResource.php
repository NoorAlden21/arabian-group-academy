<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChildBasicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Load the classroom relation explicitly if it's not already loaded
        // This ensures the classroom data is available
        $this->whenLoaded('classroom', function() {
            // No need to do anything here, just load it
        });

        // Get the classroom data, making sure the relation is loaded
        $classroom = $this->classroom;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => optional($this->user)->name,
            'classroom_id' => optional($classroom)->id,
            'classroom_name' => optional($classroom)->name,
        ];
    }
}
