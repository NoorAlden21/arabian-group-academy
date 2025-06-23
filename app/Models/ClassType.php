<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassType extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
    public function classTypeSubjects()
    {
        return $this->hasMany(ClassTypeSubject::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }
}
