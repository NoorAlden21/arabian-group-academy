<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAbsence extends Model
{
     protected $fillable = [
        'student_profile_id',
        'period',
        'absent_at',
        'status',        // 'absent' | 'late'
    ];

    protected $casts = [
        'absent_at'    => 'datetime',
        'period'       => 'integer',
        'minutes_late' => 'integer',
    ];

    public function studentProfile(){
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function scopeForDayPeriod($query, $date, int $period)
    {
        return $query->whereDate('absent_date', $date)->where('period', $period);
    }

    public function scopeAbsent($query){
        return $query->where('status', 'absent');
    }

    public function scopeLate($query){
        return $query->where('status', 'late');
    }
}
