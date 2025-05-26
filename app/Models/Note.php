<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
     protected $fillable = [
        'sender_id',
        'receiver_id',
        'title',
        'message',
        'seen_at',
    ];

    public function sender(){
        $this->belongsTo(User::class,'sender_id');
    }

    public function receiver(){
        $this->belongsTo(User::class,'receiver_id');
    }
}
