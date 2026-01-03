<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasRevisions;
use App\Services\LanguageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    /** @use HasFactory<\Database\Factories\PageFactory> */
    use HasFactory, Auditable, SoftDeletes, HasRevisions;

    const PAGE_TYPE_ROOT = 'root';
    const PAGE_TYPE_REGULAR = 'regular';
    const PAGE_TYPE_ENTITY_ARCHIVE = 'entity_archive';
    const PAGE_TYPE_404 = '404';

    protected $fillable = [
        'title',
        'slug',
        'is_root',
        'site_key',
        'page_type',
        'order',
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
        'robots_noindex',
        'system_config',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_root' => 'boolean',
        'sitemap_include' => 'boolean',
        'sitemap_priority' => 'float',
        'blocks' => 'array',
        'robots_noindex' => 'boolean',
        'system_config' => 'array',
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
        return $this->hasMany(Page::class, 'parent_id')->orderBy('order');
    }

    public static function getPageTypes(): array
    {
        return [
            self::PAGE_TYPE_ROOT => [
                'label' => 'Root/Home Page',
                'description' => 'Main homepage for a site. Each root page can have its own domain.',
                'icon' => 'home',
                'color' => 'blue'
            ],
            self::PAGE_TYPE_REGULAR => [
                'label' => 'Regular Page',
                'description' => 'Standard content page that can be nested in the page tree.',
                'icon' => 'document',
                'color' => 'gray'
            ],
            self::PAGE_TYPE_ENTITY_ARCHIVE => [
                'label' => 'Entity Archive Page',
                'description' => 'Archive page for displaying entity listings (News, Products, etc.).',
                'icon' => 'collection',
                'color' => 'purple'
            ],
            self::PAGE_TYPE_404 => [
                'label' => '404 Error Page',
                'description' => 'Custom 404 error page. Not included in sitemap.',
                'icon' => 'exclamation',
                'color' => 'red'
            ],
        ];
    }

    public function getPageTypeInfo(): array
    {
        $types = self::getPageTypes();
        return $types[$this->page_type] ?? $types[self::PAGE_TYPE_REGULAR];
    }

    public function isAncestorOf($pageId): bool
    {
        $page = is_numeric($pageId) ? Page::find($pageId) : $pageId;
        
        if (!$page) {
            return false;
        }

        $parent = $page->parent;
        
        while ($parent) {
            if ($parent->id === $this->id) {
                return true;
            }
            $parent = $parent->parent;
        }
        
        return false;
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

        // Get the root page for this page
        $rootPage = $this->is_root ? $this : $this->getRootPage();
        
        // Check if there's a configured domain from YAML for this site
        $domain = null;
        if ($rootPage && $rootPage->site_key) {
            $resourceService = new \App\Services\SiteResourceService();
            $resourceService->setSiteKey($rootPage->site_key);
            $domain = $resourceService->getPrimaryDomain();
            
            // Get default locale from site config
            $defaultLocale = $resourceService->getDefaultLocale();
        } else {
            $languageService = app(LanguageService::class);
            $defaultLocale = $languageService->getDefaultLocale();
        }

        // Build the full URL
        if ($locale !== $defaultLocale) {
            $fullPath = '/' . $locale . rtrim($path, '/');
        } else {
            $fullPath = $path;
        }

        // Use configured domain if available, otherwise use default url() helper
        if ($domain) {
            return rtrim($domain, '/') . $fullPath;
        }

        return url($fullPath);
    }

    public function getRootPage(): ?Page
    {
        $current = $this;
        
        while ($current->parent) {
            $current = $current->parent;
            if ($current->is_root) {
                return $current;
            }
        }
        
        return $current->is_root ? $current : null;
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
