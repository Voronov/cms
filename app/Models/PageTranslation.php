<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\HasMedia;

class PageTranslation extends Model
{
    use HasMedia;
    protected $fillable = [
        'page_id',
        'locale',
        'title',
        'slug',
        'blocks',
        'meta_title',
        'meta_description',
        'og_title',
        'og_description',
        'og_image',
        'is_published',
    ];

    protected $casts = [
        'blocks' => 'array',
        'is_published' => 'boolean',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
