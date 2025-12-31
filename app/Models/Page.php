<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Services\LanguageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /** @use HasFactory<\Database\Factories\PageFactory> */
    use HasFactory, Auditable;

    protected $fillable = [
        'title',
        'slug',
        'is_root',
        'layout',
        'content',
        'meta_title',
        'meta_description',
        'og_title',
        'og_description',
        'og_image',
        'is_published',
        'parent_id',
        'sitemap_include',
        'sitemap_priority',
        'sitemap_changefreq',
        'blocks',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_root' => 'boolean',
        'sitemap_include' => 'boolean',
        'sitemap_priority' => 'float',
        'blocks' => 'array',
    ];

    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function translations()
    {
        return $this->hasMany(PageTranslation::class);
    }

    public function translation(?string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations()->where('locale', $locale)->first();
    }

    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    public function redirects()
    {
        return $this->hasMany(Redirect::class);
    }

    public function getFullPath(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translation($locale);
        $slug = $translation ? $translation->slug : $this->slug;

        if ($this->is_root) {
            return '/';
        }

        $path = $slug;
        $parent = $this->parent;

        while ($parent) {
            if ($parent->is_root) {
                break;
            }
            $parentTrans = $parent->translation($locale);
            $parentSlug = $parentTrans ? $parentTrans->slug : $parent->slug;
            $path = $parentSlug . '/' . $path;
            $parent = $parent->parent;
        }

        return '/' . ltrim($path, '/');
    }

    public function getFullUrl(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $path = $this->getFullPath($locale);

        $languageService = app(LanguageService::class);
        $defaultLocale = $languageService->getDefaultLocale();

        if ($locale !== $defaultLocale) {
            return url('/' . $locale . rtrim($path, '/'));
        }

        return url($path);
    }

    /**
     * Get all ancestor pages.
     */
    public function getAncestors()
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    /**
     * Get IDs of all ancestor pages.
     */
    public function getAncestorIds(): array
    {
        return $this->getAncestors()->pluck('id')->toArray();
    }

    /**
     * Check if the page is published and all its ancestors are also published.
     */
    public function isEffectivelyPublished(): bool
    {
        if (!$this->is_published) {
            return false;
        }

        $parent = $this->parent;
        while ($parent) {
            if (!$parent->is_published) {
                return false;
            }
            $parent = $parent->parent;
        }

        return true;
    }
}
