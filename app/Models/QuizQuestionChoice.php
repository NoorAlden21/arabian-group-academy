<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class QuizQuestionChoice extends Model
{
    protected $fillable = [
        'question_id',
        'choice_text',
        'choice_image',
        'is_correct',
    ];

     public function getChoiceImageUrlAttribute()
    {
        return $this->choice_image
            ? Storage::disk('public')->url($this->choice_image)
            : null;
    }

    public function question(){
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}
