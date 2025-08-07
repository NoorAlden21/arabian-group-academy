<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizForStudentResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'started_at' => $this->started_at,
            'deadline' => $this->deadline,
            'created_at' => $this->created_at,

            'questions' => $this->whenLoaded('questions', function(){
                return $this->questions->map(function ($question){
                    return [
                        'id' => $question->id,
                        'question text' => $question->question_text,
                        'choices' => $question->choices->map(function ($choice){
                            return [
                                'id' => $choice->id,
                                'choice_text' => $choice->choice_text,
                            ];
                        }),
                    ];
                });
            }),
        ];
    }
}
