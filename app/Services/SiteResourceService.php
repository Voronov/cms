<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class SiteResourceService
{
    protected ?string $siteKey = null;

    public function setSiteKey(?string $siteKey): void
    {
        $this->siteKey = $siteKey;
    }

    public function getSiteKey(): ?string
    {
        return $this->siteKey;
    }

    public function setSiteFromPage(Page $page): void
    {
        $rootPage = $page->is_root ? $page : $page->getRootPage();
        $this->siteKey = $rootPage?->site_key;
    }

    public function getResourcePath(string $type): string
    {
        $basePath = resource_path();
        
        if ($this->siteKey) {
            $sitePath = $basePath . '/sites/' . $this->siteKey . '/' . $type;
            if (File::exists($sitePath)) {
                return $sitePath;
            }
        }
        
        // Fallback to default resources
        return $basePath . '/' . $type;
    }

    public function getEntities(): array
    {
        $entitiesPath = $this->getResourcePath('entities');
        
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
                    'config' => $content,
                ];
            }
        }

        return $entities;
    }

    public function getEntity(string $type): ?array
    {
        $entitiesPath = $this->getResourcePath('entities');
        $filePath = $entitiesPath . '/' . $type . '.yaml';
        
        if (!File::exists($filePath)) {
            return null;
        }

        return Yaml::parseFile($filePath);
    }

    public function getLayouts(): array
    {
        $layoutsPath = $this->getResourcePath('layouts');
        
        if (!File::exists($layoutsPath)) {
            return [];
        }

        $layouts = [];
        $files = File::files($layoutsPath);
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'yaml') {
                $content = Yaml::parse(File::get($file));
                $layouts[$file->getFilenameWithoutExtension()] = $content['name'] ?? $file->getFilenameWithoutExtension();
            }
        }
        
        return $layouts;
    }

    public function getBlocks(): array
    {
        $blocksPath = $this->getResourcePath('layouts');
        
        if (!File::exists($blocksPath)) {
            return [];
        }

        $blocks = [];
        $directories = File::directories($blocksPath);

        foreach ($directories as $directory) {
            $blockName = basename($directory);
            
            if ($blockName === 'admin') {
                continue;
            }
            
            $yamlFile = $directory . '/' . $blockName . '.yaml';
            
            if (File::exists($yamlFile)) {
                $config = Yaml::parseFile($yamlFile);
                $config['id'] = $blockName;
                $blocks[] = $config;
            }
        }

        return $blocks;
    }

    public function getConfig(string $configFile): array
    {
        $configPath = $this->getResourcePath('config');
        $filePath = $configPath . '/' . $configFile;
        
        if (!File::exists($filePath)) {
            // Try default location
            $filePath = resource_path($configFile);
            if (!File::exists($filePath)) {
                return [];
            }
        }

        return Yaml::parseFile($filePath);
    }

    public function getSiteConfig(): ?array
    {
        if (!$this->siteKey) {
            return null;
        }

        $configPath = resource_path('sites/' . $this->siteKey . '.yaml');
        
        if (!File::exists($configPath)) {
            return null;
        }

        return Yaml::parseFile($configPath);
    }

    public function getDomains(): array
    {
        $config = $this->getSiteConfig();
        
        if (!$config || !isset($config['domains'])) {
            return [];
        }

        return $config['domains'];
    }

    public function getPrimaryDomain(): ?string
    {
        $config = $this->getSiteConfig();
        
        return $config['primary_domain'] ?? ($config['domains'][0] ?? null);
    }

    public function getLanguages(): array
    {
        $config = $this->getSiteConfig();
        
        if ($config && isset($config['languages'])) {
            return $config['languages'];
        }

        // Fallback to global languages
        $languagesPath = resource_path('languages.yaml');
        if (File::exists($languagesPath)) {
            $globalConfig = Yaml::parseFile($languagesPath);
            return $globalConfig['languages'] ?? [];
        }

        return [];
    }

    public function getDefaultLocale(): string
    {
        $config = $this->getSiteConfig();
        
        if ($config && isset($config['default_locale'])) {
            return $config['default_locale'];
        }

        // Fallback to global default
        $languagesPath = resource_path('languages.yaml');
        if (File::exists($languagesPath)) {
            $globalConfig = Yaml::parseFile($languagesPath);
            return $globalConfig['default_locale'] ?? 'en';
        }

        return 'en';
    }

    public static function getAllSiteConfigs(): array
    {
        $sitesPath = resource_path('sites');
        
        if (!File::exists($sitesPath)) {
            return [];
        }

        $configs = [];
        $files = File::files($sitesPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'yaml') {
                $siteKey = $file->getFilenameWithoutExtension();
                if ($siteKey === 'example-site') {
                    continue; // Skip example
                }
                
                $config = Yaml::parseFile($file->getPathname());
                $configs[$siteKey] = $config;
            }
        }

        return $configs;
    }

    public function createSiteResources(string $siteKey): void
    {
        $sitePath = resource_path('sites/' . $siteKey);
        
        // Create site-specific directories
        $directories = [
            'entities',
            'layouts',
            'config',
            'crons',
        ];

        foreach ($directories as $dir) {
            $path = $sitePath . '/' . $dir;
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }
        }

        // Copy default configurations as templates
        $this->copyDefaultResources($siteKey);
    }

    private function copyDefaultResources(string $siteKey): void
    {
        $sitePath = resource_path('sites/' . $siteKey);
        
        // Copy example files
        $defaultFiles = [
            'cache.yaml',
            'media.yaml',
            'languages.yaml',
        ];

        foreach ($defaultFiles as $file) {
            $source = resource_path($file);
            $destination = $sitePath . '/' . $file;
            
            if (File::exists($source) && !File::exists($destination)) {
                File::copy($source, $destination);
            }
        }
    }
}
