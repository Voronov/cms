<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'event',
        'message',
        'context',
        'old_values',
        'new_values',
        'user_id',
        'ip_address',
        'auditable_type',
        'auditable_id',
    ];

    protected $casts = [
        'context' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function auditable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
