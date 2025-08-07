<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classroom extends Model
{
    use HasFactory,SoftDeletes;
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

    public function classType()
    {
        return $this->belongsTo(ClassType::class);
    }
    
    public function quizzes(){
        return $this->belongsToMany(Quiz::class, 'quiz_classrooms');
    }

    public function quizClassrooms(){
        return $this->hasMany(QuizClassroom::class);
    }
}
