<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class QuizQuestion extends Model
{
     protected $fillable = [
        'quiz_id',
        'question_text',
        'question_image',
    ];

    public function quiz(){
        return $this->belongsTo(Quiz::class);
    }

    public function choices(){
        return $this->hasMany(QuizQuestionChoice::class, 'question_id');
    }

    public function getQuestionImageUrlAttribute()
    {
        return $this->question_image
            ? Storage::disk('public')->url($this->question_image)
            : null;
    }
}
