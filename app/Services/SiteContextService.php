<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Facades\Cache;

class SiteContextService
{
    protected ?array $config = null;
    protected ?int $siteId = null;

    public function setSiteId(?int $siteId): void
    {
        $this->siteId = $siteId;
        $this->config = null;
    }

    public function getRoute(string $key): mixed
    {
        $config = $this->getConfig();
        
        return $config['routes'][$key] ?? null;
    }

    public function getConfig(): array
    {
        $cacheKey = $this->getCacheKey();
        
        if ($this->config !== null && Cache::has($cacheKey)) {
            return $this->config;
        }

        $this->config = Cache::rememberForever($cacheKey, function () {
            return $this->loadConfigFromDatabase();
        });

        return $this->config;
    }

    protected function loadConfigFromDatabase(): array
    {
        if ($this->siteId) {
            $rootPage = Page::where('id', $this->siteId)->where('is_root', true)->first();
        } else {
            $rootPage = Page::where('is_root', true)->first();
        }
        
        if (!$rootPage || !$rootPage->system_config) {
            return ['routes' => []];
        }

        return $rootPage->system_config;
    }

    protected function getCacheKey(): string
    {
        return 'site_config_' . ($this->siteId ?? 'default');
    }

    public static function clearCache(?int $siteId = null): void
    {
        if ($siteId) {
            Cache::forget('site_config_' . $siteId);
        } else {
            // Clear all site configs
            $rootPages = Page::where('is_root', true)->get();
            foreach ($rootPages as $rootPage) {
                Cache::forget('site_config_' . $rootPage->id);
            }
            Cache::forget('site_config_default');
        }
    }

    public function refresh(): void
    {
        self::clearCache($this->siteId);
        $this->config = null;
    }
}
