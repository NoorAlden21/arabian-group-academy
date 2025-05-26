<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TeacherProfile extends Model
{
    protected $fillable = ['user_id', 'department', 'qualification'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function homeworks(){
        return $this->hasMany(Homework::class,'teacher_id','user_id');
    }

    public function schedules(){
        return $this->hasMany(Schedule::class,'teacher_id','user_id');
    }

     public function tomorrowSchedules(){
        $day = strtolower(Carbon::tomorrow()->format('l'));
        return $this->schedules()->where('day',$day)->get();
    }

    public function classrooms(){
        return $this->hasManyThrough(
            Classroom::class,
            ClassSubjectTeacher::class,
            'teacher_id',
            'id',
            'user_id',
            'classroom_id'
        )->distinct();
    }

    public function students(){
        return $this->classrooms->flatMap->students->unique('id');  //eager load $teacherProfile->load('classrooms.students');
    }
}
