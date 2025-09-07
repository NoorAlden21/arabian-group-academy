<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherRating extends Model
{
    protected $fillable = [
        'student_profile_id',
        'teacher_profile_id',
        'academic_year',
        'rating',
        'note',
    ];

    protected $casts = [
        'rating' => 'float',
    ];

    public function studentProfile()
    {
        return $this->belongsTo(\App\Models\StudentProfile::class, 'student_profile_id');
    }

    public function teacherProfile()
    {
        return $this->belongsTo(\App\Models\TeacherProfile::class, 'teacher_profile_id');
    }
}
