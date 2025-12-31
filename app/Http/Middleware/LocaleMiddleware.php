<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    protected $languageService;

    public function __construct(\App\Services\LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $segments = $request->segments();
        $firstSegment = count($segments) > 0 ? $segments[0] : null;

        $locales = $this->languageService->getLanguages();
        $defaultLocale = $this->languageService->getDefaultLocale();

        if ($firstSegment && isset($locales[$firstSegment])) {
            app()->setLocale($firstSegment);
        } else {
            app()->setLocale($defaultLocale);
        }

        return $next($request);
    }
}
