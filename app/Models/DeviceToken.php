<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends Model
{
    protected $fillable = ['user_id', 'platform', 'token', 'locale', 'active', 'last_seen_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
