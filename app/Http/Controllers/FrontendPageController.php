<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Redirect;
use Illuminate\Http\Request;
use App\Services\LanguageService;
use Symfony\Component\Yaml\Yaml;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class FrontendPageController extends Controller
{
    protected $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }
    /**
     * Handle the incoming request.
     */
    public function show(Request $request, string $path = '/')
    {
        // Load cache configuration
        $cacheConfig = Yaml::parseFile(resource_path('cache.yaml'));
        $cacheEnabled = $cacheConfig['pages']['enabled'] ?? true;
        $cacheTtl = $cacheConfig['pages']['ttl'] ?? 300;
        
        $locale = app()->getLocale();
        $cacheKey = "page:{$locale}:{$path}";
        
        // Try to get from cache
        if ($cacheEnabled) {
            $cachedHtml = Cache::get($cacheKey);
            if ($cachedHtml) {
                return response($cachedHtml);
            }
        }
        
        $mediaConfig = Yaml::parseFile(resource_path('media.yaml'));
        $defaultLocale = $this->languageService->getDefaultLocale();

        if ($path === '/' || $path === '') {
            $page = Page::where('is_root', true)->where('is_published', true)->first();
            if ($page) {
                $translation = $page->translation($locale);
                if ($translation && $translation->is_published) {
                    return view('page', compact('page', 'translation', 'mediaConfig'));
                }
            }
            abort(404);
        }

        $segments = explode('/', ltrim($path, '/'));

        // Strip locale prefix if present
        if ($segments[0] === $locale && $locale !== $defaultLocale) {
            array_shift($segments);
            if (empty($segments)) {
                // If it was just "/es", attempt to load root page for that locale
                $page = Page::where('is_root', true)->where('is_published', true)->first();
                if ($page) {
                    $translation = $page->translation($locale);
                    if (!$translation) {
                        // Fallback to default locale translation
                        $defaultLocale = $this->languageService->getDefaultLocale();
                        $translation = $page->translation($defaultLocale);
                    }
                    if ($translation && $translation->is_published) {
                        return view('page', compact('page', 'translation', 'mediaConfig'));
                    }
                }
                abort(404);
            }
        }

        $parentId = null;
        $page = null;

        foreach ($segments as $segment) {
            $page = Page::whereHas('translations', function ($query) use ($segment, $locale) {
                $query->where('slug', $segment)
                    ->where('locale', $locale)
                    ->where('is_published', true);
            })
                ->where('is_published', true) // Global publish status
                ->where('parent_id', $parentId)
                ->first();

            // If not found in current locale, try default locale
            if (!$page && $locale !== $defaultLocale) {
                $page = Page::whereHas('translations', function ($query) use ($segment, $defaultLocale) {
                    $query->where('slug', $segment)
                        ->where('locale', $defaultLocale)
                        ->where('is_published', true);
                })
                    ->where('is_published', true)
                    ->where('parent_id', $parentId)
                    ->first();
            }

            if (!$page) {
                $redirect = Redirect::where('from_url', '/' . ltrim($path, '/'))->first();
                if ($redirect) {
                    return redirect($redirect->to_url, $redirect->status_code);
                }
                abort(404);
            }

            $parentId = $page->id;
        }

        $translation = $page->translation($locale);
        
        // Fallback to default locale if translation not found
        if (!$translation && $locale !== $defaultLocale) {
            $translation = $page->translation($defaultLocale);
        }

        $view = view('page', compact('page', 'translation', 'mediaConfig'));
        
        // Cache the rendered HTML
        if ($cacheEnabled) {
            $html = $view->render();
            Cache::put($cacheKey, $html, $cacheTtl);
        }
        
        return $view;
    }
}
