<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherClassTypeSubject extends Model
{
    protected $guarded = [];

    public function classTypeSubject(){
        return $this->belongsTo(ClassTypeSubject::class);
    }

    public function teachers(){
        return $this->belongsToMany(
            TeacherProfile::class,
            'teacher_class_type_subjects', //pivot table
            'class_type_subject_id',       //foreing for this model
            'teacher_profile_id'           //foreign for the teachers
        );
    }
}
