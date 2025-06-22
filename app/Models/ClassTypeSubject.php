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
    
}
