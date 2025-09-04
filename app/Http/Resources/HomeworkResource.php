<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use App\Models\HomeworkStudentStatus;

class HomeworkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isCompleted = false;
        if (Auth::check() && Auth::user()->hasRole('student')) {
            $isCompleted = (bool) HomeworkStudentStatus::where('homework_id', $this->id)
                ->where('student_profile_id', optional(Auth::user()->studentProfile)->id)
                ->exists();
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'due_time' => $this->due_time,
            'created_at' => $this->created_at,
            'class' => optional(optional($this->classSubjectTeacher)->classroom)->name,
            'subject' => optional(optional($this->classSubjectTeacher)->subject)->name,
            'is_completed' => $isCompleted,
            'status' => $isCompleted ? 'completed' : 'pending'
        ];
    }
}
