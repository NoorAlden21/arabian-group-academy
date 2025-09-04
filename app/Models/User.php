<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone_number',
        'gender',
        'mother_name',
        'birth_date',
        'password',
    ];

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function teacherProfile()
    {
        return $this->hasOne(TeacherProfile::class);
    }

    public function parentProfile()
    {
        return $this->hasOne(ParentProfile::class);
    }


    public function isStudent(): bool
    {
        return $this->studentProfile()->exists();
    }

    public function isTeacher(): bool
    {
        return $this->teacherProfile()->exists();
    }

    public function isParent(): bool
    {
        return $this->parentProfile()->exists();
    }



    public function sentNotes()
    {
        return $this->hasMany(Note::class, 'sender_id');
    }

    public function receivedNotes()
    {
        return $this->hasMany(Note::class, 'receiver_id');
    }

    public function classSubjectTeachers()
    {
        return $this->hasManyThrough(
            \App\Models\ClassSubjectTeacher::class,
            \App\Models\TeacherProfile::class,
            'user_id',                // Foreign key on TeacherProfile
            'teacher_profile_id',     // Foreign key on ClassSubjectTeacher
            'id',                     // Local key on User
            'id'                      // Local key on TeacherProfile
        );
    }

    public function parentChildren()
    {
        return $this->hasMany(StudentProfile::class, 'parent_id', 'id');
    }


    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }
}
