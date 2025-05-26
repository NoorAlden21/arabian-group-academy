<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = ['name', 'level', 'year', 'students_count'];

    public function students()
    {
        return $this->hasMany(StudentProfile::class);
    }

    public function classSubjectTeachers()
    {
        return $this->hasMany(ClassSubjectTeacher::class);
    }

    public function schedules()
    {
        return $this->hasManyThrough(
            Schedule::class,
            ClassSubjectTeacher::class,
            'classroom_id',                  // FK on ClassSubjectTeacher
            'class_subject_teacher_id',     // FK on Schedule
            'id',                            // PK on Classroom
            'id'                             // PK on ClassSubjectTeacher
        );
    }

     public function homeworks()
    {
        return $this->hasManyThrough(
            Homework::class,
            ClassSubjectTeacher::class,
            'classroom_id',                  // FK on ClassSubjectTeacher
            'class_subject_teacher_id',     // FK on Homework
            'id',                            // PK on Classroom
            'id'                             // PK on ClassSubjectTeacher
        );
    }
}
