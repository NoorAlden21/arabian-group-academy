<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherClassTypeSubject extends Model
{
    protected $guarded = [];
    public function classTypeSubject(){
        return $this->belongsTo(ClassTypeSubject::class);
    }
}
