<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'class_subject_teacher_id',
        'title',
        'description',
        'deadline',
        'started_at',
        'is_published',
    ];

    public function classSubjectTeacher(){
        return $this->belongsTo(ClassSubjectTeacher::class);
    }

    public function questions(){
        return $this->hasMany(QuizQuestion::class);
    }

    public function submissions(){
        return $this->hasMany(QuizSubmission::class);
    }
}
