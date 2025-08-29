<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'teacher_profile_id',
        'subject_id',
        'title',
        'description',
        'deadline',
        'started_at',
        'is_published',
    ];

    public function teacher(){
        return $this->belongsTo(TeacherProfile::class, 'teacher_profile_id');
    }

    public function classrooms(){
        return $this->belongsToMany(Classroom::class, 'quiz_classrooms');
    }
    public function questions(){
        return $this->hasMany(QuizQuestion::class);
    }

    public function submissions(){
        return $this->hasMany(QuizSubmission::class);
    }

    public function subject(){
        return $this->belongsTo(Subject::class);
    }
}
