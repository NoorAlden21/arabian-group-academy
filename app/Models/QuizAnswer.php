<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    protected $fillable = [
        'submission_id',
        'question_id',
        'selected_choice_id',
    ];

    public function submission(){
        return $this->belongsTo(QuizSubmission::class);
    }

    public function question(){
        return $this->belongsTo(QuizQuestion::class);
    }

    public function selectedChoice(){
        return $this->belongsTo(QuizQuestionChoice::class, 'selected_choice_id');
    }
}
