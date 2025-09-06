<?php

namespace App\Http\Resources;

use App\Models\Complaint;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintAdminResource extends JsonResource
{
    public function toArray($request): array
    {
        $complainantUser = method_exists($this->complainantable, 'user') ? $this->complainantable?->user : null;
        $targetUser      = method_exists($this->targetable, 'user') ? $this->targetable?->user : null;

        return [
            'id'          => $this->id,
            'topic'       => $this->topic,
            'description' => $this->description,
            'status'      => $this->status,

            'complainant' => [
                'profile_type' => Complaint::labelFromClass($this->complainantable_type), // student/teacher
                'profile_id'   => $this->complainantable_id,
                'name'         => $complainantUser?->name,
            ],

            'target' => [
                'profile_type' => Complaint::labelFromClass($this->targetable_type), // student/teacher
                'profile_id'   => $this->targetable_id,
                'name'         => $targetUser?->name,
            ],

            'handled_by' => $this->handler ? [
                'user_id' => $this->handler->id,
                'name'    => $this->handler->name,
            ] : null,

            'handled_at' => $this->handled_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
