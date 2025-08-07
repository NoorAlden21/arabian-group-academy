<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizClassroom extends Model
{
    protected $fillable = ['quiz_id', 'classroom_id'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}
