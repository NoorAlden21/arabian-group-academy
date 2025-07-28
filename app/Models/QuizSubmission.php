<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizSubmission extends Model
{
    protected $fillable = [
        'quiz_id',
        'student_profile_id',
        'score',
        'submitted_at',
    ];

    public function quiz(){
        return $this->belongsTo(Quiz::class);
    }

    public function student(){
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function answers(){
        return $this->hasMany(QuizAnswer::class, 'submission_id');
    }
}
