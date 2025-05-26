<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
      protected $fillable = [
        'class_subject_teacher_id',
        'day',
        'period',
        'start_time',
        'end_time',
    ];

    public function classSubjectTeacher()
    {
        return $this->belongsTo(ClassSubjectTeacher::class);
    }

    public function subject()
    {
        return $this->classSubjectTeacher->subject;
    }

    public function teacher()
    {
        return $this->classSubjectTeacher->teacher;
    }

    public function classroom()
    {
        return $this->classSubjectTeacher->classroom;
    }
}
