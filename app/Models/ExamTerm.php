<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamTerm extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name','academic_year','term','start_date','end_date','status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function exams() {
        return $this->hasMany(Exam::class);
    }
}
