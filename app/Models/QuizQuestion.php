<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
     protected $fillable = [
        'quiz_id',
        'question_text',
    ];

    public function quiz(){
        return $this->belongsTo(Quiz::class);
    }

    public function choices(){
        return $this->hasMany(QuizQuestionChoice::class, 'question_id');
    }
}
