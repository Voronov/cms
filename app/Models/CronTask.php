<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CronTask extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name',
        'slug',
        'command',
        'schedule',
        'is_enabled',
        'last_run_at',
        'next_run_at',
        'last_output',
        'last_status',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];
}
