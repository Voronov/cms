<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'page_id',
        'url',
        'anchor',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function translations()
    {
        return $this->hasMany(MenuItemTranslation::class);
    }

    public function translation(?string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations()->where('locale', $locale)->first();
    }

    public function getTitle(?string $locale = null)
    {
        $translation = $this->translation($locale);
        return $translation ? $translation->title : '';
    }

    public function getUrl(?string $locale = null)
    {
        if ($this->page_id) {
            return $this->page->getFullUrl($locale) . ($this->anchor ? '#' . ltrim($this->anchor, '#') : '');
        }

        if ($this->url) {
            return $this->url . ($this->anchor ? '#' . ltrim($this->anchor, '#') : '');
        }

        return $this->anchor ? '#' . ltrim($this->anchor, '#') : '#';
    }
}
