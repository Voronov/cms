<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['site_id', 'key', 'value', 'type'];

    public function site()
    {
        return $this->belongsTo(Page::class, 'site_id');
    }

    public static function get(int $siteId, string $key, $default = null)
    {
        return Cache::remember("site_setting_{$siteId}_{$key}", 3600, function () use ($siteId, $key, $default) {
            $setting = self::where('site_id', $siteId)->where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    public static function set(int $siteId, string $key, $value, string $type = 'string'): void
    {
        self::updateOrCreate(
            ['site_id' => $siteId, 'key' => $key],
            ['value' => $value, 'type' => $type]
        );

        Cache::forget("site_setting_{$siteId}_{$key}");
    }

    public static function has(int $siteId, string $key): bool
    {
        return self::where('site_id', $siteId)->where('key', $key)->exists();
    }

    public static function getAllForSite(int $siteId): array
    {
        return Cache::remember("site_settings_{$siteId}", 3600, function () use ($siteId) {
            $settings = self::where('site_id', $siteId)->get();
            $result = [];
            
            foreach ($settings as $setting) {
                $result[$setting->key] = self::castValue($setting->value, $setting->type);
            }
            
            return $result;
        });
    }

    private static function castValue($value, string $type)
    {
        return match($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'float' => (float) $value,
            'array', 'json' => json_decode($value, true),
            default => $value,
        };
    }

    public static function clearCache(?int $siteId = null): void
    {
        if ($siteId) {
            Cache::forget("site_settings_{$siteId}");
        } else {
            Cache::flush();
        }
    }
}
