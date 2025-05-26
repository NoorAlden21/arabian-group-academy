<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Homework extends Model
{
    protected $guarded = [];

    public function classSubjectTeacher()
    {
        return $this->belongsTo(ClassSubjectTeacher::class);
    }

    public function subject()
    {
        return $this->classSubjectTeacher?->subject;
    }

    public function teacher()
    {
        return $this->classSubjectTeacher?->teacher;
    }

    public function classroom()
    {
        return $this->classSubjectTeacher?->classroom;
    }

   public function students(){
        return $this->classSubjectTeacher->classroom->students;
    }

}
