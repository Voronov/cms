<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntityTranslation extends Model
{
    protected $fillable = [
        'entity_id',
        'locale',
        'content',
        'seo',
    ];

    protected $casts = [
        'content' => 'array',
        'seo' => 'array',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }
}
