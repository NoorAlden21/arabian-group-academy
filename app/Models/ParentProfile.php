<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentProfile extends Model
{
    use SoftDeletes;
    protected $fillable = ['user_id','phone_number','occupation'];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function children(){
        return $this->hasMany(StudentProfile::class,'parent_id');
    }
}
