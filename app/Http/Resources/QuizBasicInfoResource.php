<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizBasicInfoResource extends JsonResource
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
            'subject' => $this->whenLoaded('subject', function () {
                return [
                    'id' => $this->subject->id,
                    'name' => $this->subject->name,
                ];
            }),
            'title' => $this->title,
            'description' => $this->description,
            'started_at' => $this->started_at,
            'deadline' => $this->deadline,
            'is_published' => (bool) $this->is_published,
            'created_at' => $this->created_at,
        ];
    }
}
