<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Page;
use App\Services\SiteResourceService;
use Illuminate\Support\Facades\Cache;

class DetectSite
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $scheme = $request->getScheme();
        $currentDomain = $scheme . '://' . $host;
        
        // Try to find a root page that matches this domain from YAML configs
        $siteId = Cache::remember("site_domain:{$currentDomain}", 3600, function () use ($currentDomain) {
            $siteConfigs = SiteResourceService::getAllSiteConfigs();
            
            // Normalize current domain for comparison
            $normalizedCurrent = rtrim($currentDomain, '/');
            
            foreach ($siteConfigs as $siteKey => $config) {
                if (!isset($config['domains'])) {
                    continue;
                }
                
                // Check if current domain matches any configured domain
                foreach ($config['domains'] as $configuredDomain) {
                    $normalizedConfigured = rtrim($configuredDomain, '/');
                    
                    if ($normalizedConfigured === $normalizedCurrent) {
                        // Find the root page with this site_key
                        $rootPage = Page::where('site_key', $siteKey)
                            ->where('is_root', true)
                            ->first();
                        
                        if ($rootPage) {
                            return $rootPage->id;
                        }
                    }
                }
            }
            
            // If no match found, return the first root page or null
            $firstRoot = Page::where('is_root', true)->first();
            return $firstRoot?->id;
        });
        
        // Store the site ID in the request for use in controllers
        $request->attributes->set('site_id', $siteId);
        
        return $next($request);
    }
}
