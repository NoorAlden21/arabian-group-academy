<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
     protected $fillable = [
        'student_profile_id','gradable_type','gradable_id',
        'score','max_score','status','remark',
        'graded_at','verified_at',
    ];

    protected $casts = [
        'graded_at'   => 'datetime',
        'verified_at' => 'datetime',
        'score'       => 'decimal:2',
        'max_score'   => 'decimal:2',
    ];

    public function gradable() {
        return $this->morphTo();
    }

    public function studentProfile() {
        return $this->belongsTo(StudentProfile::class);
    }
}
