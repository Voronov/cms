<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        // Load all root pages for site selection
        $allRootPages = Page::where('is_root', true)->with('translations')->get();
        
        $siteId = $request->query('site_id');
        
        // If no site_id provided, use the first root page
        if (!$siteId && $allRootPages->isNotEmpty()) {
            $siteId = $allRootPages->first()->id;
        }
        
        if (!$siteId) {
            return redirect()->route('admin.pages.index')->with('error', 'Please create a root page first.');
        }

        $site = Page::where('id', $siteId)->where('is_root', true)->firstOrFail();
        
        // Load site configuration from YAML
        $resourceService = new \App\Services\SiteResourceService();
        
        // If site_key is not set in database, try to detect it from YAML files
        $siteKey = $site->site_key;
        if (!$siteKey) {
            $siteKey = $this->detectSiteKeyFromYaml($siteId);
            if ($siteKey) {
                // Auto-update the database with detected site_key
                $site->update(['site_key' => $siteKey]);
                $site->refresh();
            }
        }
        
        if ($siteKey) {
            $resourceService->setSiteKey($siteKey);
        }
        
        $siteConfig = $resourceService->getSiteConfig();
        $availableDomains = $siteConfig['domains'] ?? [];
        $primaryDomain = $siteConfig['primary_domain'] ?? ($availableDomains[0] ?? '');
        $languages = $resourceService->getLanguages();
        
        $notFoundPageId = SiteSetting::get($siteId, '404_page_id', null);
        $notFoundPage = $notFoundPageId ? Page::find($notFoundPageId) : null;
        
        $pages = Page::with('translations')->get();
        
        $entitySettings = SiteSetting::get($siteId, 'entity_settings', []);
        $entities = $this->getEntityTypes();
        
        // Get domains used by other sites
        $usedDomains = $this->getUsedDomainsBySites($siteId);
        
        return view('admin.settings.index', compact('site', 'allRootPages', 'availableDomains', 'primaryDomain', 'notFoundPage', 'pages', 'entitySettings', 'entities', 'languages', 'siteConfig', 'usedDomains'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:pages,id',
            'site_key' => 'required|string|max:255|regex:/^[a-z0-9\-]+$/',
            'primary_domain' => 'nullable|string|max:255',
            '404_page_id' => 'nullable|exists:pages,id',
            'entity_settings' => 'nullable|array',
        ]);

        $siteId = $request->input('site_id');
        $site = Page::where('id', $siteId)->where('is_root', true)->firstOrFail();

        $oldSiteKey = $site->site_key;
        $newSiteKey = $request->input('site_key');

        // Check if site_key is unique among root pages
        $existingPage = Page::where('site_key', $newSiteKey)
            ->where('is_root', true)
            ->where('id', '!=', $siteId)
            ->first();
        
        if ($existingPage) {
            return back()->withErrors(['site_key' => 'This site key is already in use by another site.'])->withInput();
        }

        // Update site_key
        $site->update(['site_key' => $newSiteKey]);

        // Create site-specific resource folders if they don't exist
        $resourceService = new \App\Services\SiteResourceService();
        $resourceService->createSiteResources($newSiteKey);

        // Store 404 page and entity settings in database (not in YAML)
        SiteSetting::set($siteId, '404_page_id', $request->input('404_page_id'), 'integer');
        SiteSetting::set($siteId, 'entity_settings', json_encode($request->input('entity_settings', [])), 'json');

        // Clear all relevant caches
        SiteSetting::clearCache($siteId);
        \App\Services\SiteContextService::clearCache($siteId);
        
        // Clear domain detection cache for all domains
        \Illuminate\Support\Facades\Cache::flush();

        $message = 'Settings updated successfully. ';
        if (!File::exists(resource_path('sites/' . $newSiteKey . '.yaml'))) {
            $message .= 'Please create the YAML configuration file at resources/sites/' . $newSiteKey . '.yaml to configure domains and languages.';
        }

        return redirect()->route('admin.settings.index', ['site_id' => $siteId])->with('success', $message);
    }

    private function detectSiteKeyFromYaml(int $siteId): ?string
    {
        $sitesPath = resource_path('sites');
        if (!File::exists($sitesPath)) {
            return null;
        }

        $files = File::files($sitesPath);
        foreach ($files as $file) {
            if ($file->getExtension() === 'yaml') {
                $content = Yaml::parseFile($file->getPathname());
                
                // Check if this YAML file references this site_id
                if (isset($content['site_id']) && $content['site_id'] == $siteId) {
                    return $file->getFilenameWithoutExtension();
                }
                
                // If site_key is defined in the YAML and matches the filename
                if (isset($content['site_key'])) {
                    $yamlSiteKey = $content['site_key'];
                    $filenameSiteKey = $file->getFilenameWithoutExtension();
                    
                    // Return the filename as the site_key (prefer filename over YAML content)
                    if ($yamlSiteKey === $filenameSiteKey) {
                        return $filenameSiteKey;
                    }
                }
            }
        }

        // If no match found, return the first non-example YAML file
        foreach ($files as $file) {
            if ($file->getExtension() === 'yaml') {
                $siteKey = $file->getFilenameWithoutExtension();
                if ($siteKey !== 'example-site') {
                    return $siteKey;
                }
            }
        }

        return null;
    }

    private function getUsedDomainsBySites(int $excludeSiteId): array
    {
        $usedDomains = [];
        $rootPages = Page::where('is_root', true)
            ->where('id', '!=', $excludeSiteId)
            ->get();
        
        foreach ($rootPages as $rootPage) {
            if ($rootPage->site_key) {
                $resourceService = new \App\Services\SiteResourceService();
                $resourceService->setSiteKey($rootPage->site_key);
                $config = $resourceService->getSiteConfig();
                
                if ($config && isset($config['domains'])) {
                    foreach ($config['domains'] as $domain) {
                        $usedDomains[$domain] = [
                            'site_id' => $rootPage->id,
                            'site_key' => $rootPage->site_key,
                            'site_name' => $rootPage->translations->first()->title ?? 'Untitled',
                        ];
                    }
                }
            }
        }
        
        return $usedDomains;
    }

    private function getEntityTypes(): array
    {
        $entitiesPath = resource_path('entities');
        if (!File::exists($entitiesPath)) {
            return [];
        }

        $entities = [];
        $files = File::files($entitiesPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'yaml') {
                $content = Yaml::parseFile($file->getPathname());
                $entityType = $file->getFilenameWithoutExtension();
                
                $entities[$entityType] = [
                    'name' => $content['name'] ?? ucfirst($entityType),
                    'singular' => $content['singular'] ?? ucfirst($entityType),
                    'icon' => $content['icon'] ?? 'document',
                    'root_integration' => $content['root_integration'] ?? null,
                ];
            }
        }

        return $entities;
    }
}
