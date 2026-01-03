<?php

namespace App\Models;

use App\Models\PageTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'filename',
        'original_name',
        'path',
        'mime_type',
        'size',
        'width',
        'height',
        'variants',
    ];

    protected $casts = [
        'variants' => 'array',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }

    public function getUsage()
    {
        $usage = [];
        
        // Check Page Blocks
        $pages = PageTranslation::where('blocks', 'LIKE', '%' . $this->path . '%')
            ->orWhere('blocks', 'LIKE', '%' . $this->id . '%')
            ->get();

        foreach ($pages as $page) {
            $usage[] = [
                'type' => 'Page',
                'name' => $page->title,
                'url' => route('admin.pages.edit', ['page' => $page->page_id, 'locale' => $page->locale])
            ];
        }

        return $usage;
    }
}
