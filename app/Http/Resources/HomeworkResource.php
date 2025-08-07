<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeworkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'due_time' => $this->due_time,
            'created_at' => $this->created_at,
            'class' => optional($this->classSubjectTeacher->classroom)->name,
            'subject' => optional($this->classSubjectTeacher->subject)->name,
        ];
    }
}
