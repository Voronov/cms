<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revision extends Model
{
    protected $fillable = [
        'revisionable_id',
        'revisionable_type',
        'content',
        'description',
        'user_id',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function revisionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
