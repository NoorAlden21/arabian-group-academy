<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'exam_term_id','class_type_id','subject_id','scheduled_at','duration_minutes',
        'max_score','status','published_at','results_published_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'results_published_at' => 'datetime',
    ];

    public function term() {
        return $this->belongsTo(ExamTerm::class, 'exam_term_id');
    }

    public function classType() {
        return $this->belongsTo(ClassType::class);
    }

    public function subject() {
        return $this->belongsTo(Subject::class);
    }

    public function grades() {
        return $this->morphMany(Grade::class, 'gradable');
    }
}
