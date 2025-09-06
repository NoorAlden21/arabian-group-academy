<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'topic',
        'description',
        'status',
        'handled_by_user_id',
        'handled_at',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    // ثابت المواضيع (Topics Only)
    public const TOPICS = [
        'سلوك غير لائق',
        'تنمر',
        'إزعاج في الحصة',
        'اعتداء لفظي',
        'اعتداء جسدي',
        'غياب متكرر',
        'غش أكاديمي',
        'أخرى',
    ];

    public const STATUSES = ['pending', 'in_review', 'resolved', 'rejected'];

    /* Morph relations */
    public function complainantable()
    {
        return $this->morphTo();
    }

    public function targetable()
    {
        return $this->morphTo();
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by_user_id');
    }

    /* Helpers */
    public static function labelFromClass(?string $class): ?string
    {
        return match ($class) {
            \App\Models\StudentProfile::class => 'student',
            \App\Models\TeacherProfile::class => 'teacher',
            default => null,
        };
    }

    public static function classFromLabel(string $label): ?string
    {
        return match ($label) {
            'student' => \App\Models\StudentProfile::class,
            'teacher' => \App\Models\TeacherProfile::class,
            default => null,
        };
    }
}
