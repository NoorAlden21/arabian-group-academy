<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasDisplayName;

class ClassType extends Model
{
    use HasFactory, HasDisplayName;
    protected $fillable = ['name', 'name_ar'];
    protected $appends  = ['display_name']; // keeps "name" intact

    public function classTypeSubjects()
    {
        return $this->hasMany(ClassTypeSubject::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_type_subjects')
            ->withTimestamps();
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    public function teachers()
    {
        return $this->hasManyThrough(
            TeacherProfile::class,                  // Final model you want
            TeacherClassTypeSubject::class,         // Intermediate (pivot) model
            'class_type_subject_id',                // FK on TeacherClassTypeSubject → ClassTypeSubject
            'id',                                   // FK on TeacherProfile → teacher_profile_id in TeacherClassTypeSubject
            'id',                                   // Local key on ClassType → ClassTypeSubject
            'teacher_profile_id'                    // Local key on TeacherClassTypeSubject → TeacherProfile
        );
    }
}
