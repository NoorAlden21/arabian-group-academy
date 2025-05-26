<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentProfile extends Model
{
    protected $fillable = ['user_id','phone_number'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function children(){
        return $this->hasMany(StudentProfile::class,'parent_id');
    }
}
