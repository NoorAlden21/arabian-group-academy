<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassTypeSubject extends Model
{
    protected $guarded = [];
    public function classType(){
        return $this->belongsTo(ClassType::class);
    }

    public function subject(){
        return $this->belongsTo(Subject::class);
    }
    
    public function teachers(){
        return $this->belongsToMany(
            TeacherProfile::class, 
            'teacher_class_type_subjects', 
            'class_type_subject_id', 
            'teacher_profile_id'
        );
    }
}
