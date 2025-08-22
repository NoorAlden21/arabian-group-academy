<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name'];

    public function classSubjectTeachers()
    {
        return $this->hasMany(ClassSubjectTeacher::class);
    }

    public function classTypeSubjects()
    {
        return $this->hasMany(ClassTypeSubject::class);
    }

    public function classTypes()
    {
        return $this->belongsToMany(ClassType::class, 'class_type_subjects')
                    ->withTimestamps();
    }

//     public function schedules()
//     {
//         return $this->hasManyThrough(
//             Schedule::class,
//             ClassSubjectTeacher::class,
//             'subject_id',                // FK on ClassSubjectTeacher → Subject
//             'class_subject_teacher_id', // FK on Schedule → ClassSubjectTeacher
//             'id',                        // PK on Subject
//             'id'                         // PK on ClassSubjectTeacher
//         );
//     }

//     public function homeworks()
//     {
//         return $this->hasManyThrough(
//             Homework::class,
//             ClassSubjectTeacher::class,
//             'subject_id',
//             'class_subject_teacher_id',
//             'id',
//             'id'
//         );
//     }
}