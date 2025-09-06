<?php

namespace App\Http\Resources;

use App\Models\Complaint;
use Illuminate\Http\Resources\Json\JsonResource;

class MyComplaintResource extends JsonResource
{
    public function toArray($request): array
    {
        //we hide the targeted user
        return [
            'id'          => $this->id,
            'target_type' => Complaint::labelFromClass($this->targetable_type), // 'student' | 'teacher'
            'topic'       => $this->topic,
            'description' => $this->description,
            'status'      => $this->status,
            'created_at'  => $this->created_at?->toDateTimeString(),
        ];
    }
}
