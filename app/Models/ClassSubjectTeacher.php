<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSubjectTeacher extends Model
{
    use HasFactory;
    protected $fillable = ['classroom_id','subject_id','teacher_profile_id'];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(){
        return $this->belongsTo(TeacherProfile::class, 'teacher_profile_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function homeworks()
    {
        return $this->hasMany(Homework::class);
    }
}

