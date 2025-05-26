<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = ['user_id', 'classroom_id', 'enrollment_year', 'parent_id','level','gpa','previous_status'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function parent(){
        return $this->belongsTo(User::class,'parent_id');
    }

    public function classroom(){
        return $this->belongsTo(Classroom::class);
    }

    public function subjects(){
        return $this->hasManyThrough(
            Subject::class,
            ClassSubjectTeacher::class,
            'classroom_id',
            'id',
            'classroom_id',
            'subject_id'
        )->distinct();
    }

    public function schedules(){
        return $this->hasManyThrough(
            Schedule::class,     // 🔹 Final model you want to reach (schedules)
            Classroom::class,    // 🔸 Intermediate model you go through (classrooms)
            'id',                // 🔸 FK on classrooms (classrooms.id) that Schedule uses (classroom_id)
            'classroom_id',      // 🔹 FK on schedules (schedules.classroom_id)
            'classroom_id',      // 🔸 FK on this model (student_profiles.classroom_id)
            'id'                 // 🔸 Local key on classrooms (classrooms.id)
        );
    }

    public function tomorrowSchedules(){
        $day = strtolower(Carbon::tomorrow()->format('l'));
        return $this->schedules()->where('day',$day)->get();
    }
    
    public function homeworks(){
        return $this->hasManyThrough(
            Homework::class,
            Classroom::class,
            'id',
            'classroom_id',
            'classroom_id',
            'id'
        );
    }
}
