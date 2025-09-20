<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyExamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'status'           => $this->status,
            'scheduled_at'     => $this->scheduled_at?->toIso8601String(),
            'duration_minutes' => $this->duration_minutes,
            'room'             => $this->room,
            'max_score'        => $this->max_score,

            'subject' => $this->when($this->relationLoaded('subject'), fn () => [
                'id'   => $this->subject?->id,
                'name' => $this->subject?->display_name,
            ]),

            'term' => $this->when($this->relationLoaded('term'), fn () => [
                'id'            => $this->term?->id,
                'name'          => $this->term?->name,
                'academic_year' => $this->term?->academic_year,
                'kind'          => $this->term?->term,
                'status'        => $this->term?->status,
            ]),

            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
