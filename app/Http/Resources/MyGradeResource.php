<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyGradeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'grade_id'    => $this->grade_id,
            'score'       => $this->score !== null ? (float) $this->score : null,
            'max_score'   => $this->max_score !== null ? (float) $this->max_score : null,
            'status'      => $this->grade_status, // present|absent|...
            'remark'      => $this->remark,
            'graded_at'   => $this->graded_at,
            'verified_at' => $this->verified_at,

            'exam' => [
                'id'                  => $this->exam_id,
                'scheduled_at'        => $this->scheduled_at,
                'duration_minutes'    => $this->duration_minutes,
                'exam_max_score'      => $this->exam_max_score,
                'results_published_at'=> $this->results_published_at,
            ],

            'subject' => [
                'id'   => $this->subject_id,
                'name' => $this->subject_name,
            ],

            'term' => [
                'id'            => $this->term_id,
                'name'          => $this->term_name,
                'academic_year' => $this->academic_year,
                'kind'          => $this->term_kind,
            ],
        ];
    }
}
