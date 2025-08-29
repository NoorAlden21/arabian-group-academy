<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizFullInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'subject' => $this->whenLoaded('subject', function () {
                return [
                    'id' => $this->subject->id,
                    'name' => $this->subject->name,
                ];
            }),
            'description' => $this->description,
            'started_at'  => $this->started_at,
            'deadline'    => $this->deadline,
            'is_published'=> (bool) $this->is_published,
            'questions'   => $this->whenLoaded('questions', function () {
                return $this->questions->map(function ($question) {
                    return [
                        'id'                 => $question->id,
                        'question_text'      => $question->question_text,      
                        'question_image_url' => $question->question_image_url, 
                        'choices'            => $question->choices->map(function ($choice) {
                            return [
                                'id'               => $choice->id,
                                'choice_text'      => $choice->choice_text,     
                                'choice_image_url' => $choice->choice_image_url,
                                'is_correct'       => (bool) $choice->is_correct,
                            ];
                        }),
                    ];
                });
            }),
        ];
    }
}
