<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class LanguageService
{
    protected array $config;

    public function __construct()
    {
        $path = resource_path('languages.yaml');
        if (File::exists($path)) {
            $this->config = Yaml::parseFile($path);
        } else {
            $this->config = [
                'default_locale' => 'en',
                'languages' => [
                    'en' => ['name' => 'English', 'mode' => 'independent']
                ]
            ];
        }
    }

    public function getLanguages(): array
    {
        return $this->config['languages'] ?? [];
    }

    public function getDefaultLocale(): string
    {
        return $this->config['default_locale'] ?? 'en';
    }

    public function getSupportedLocales(): array
    {
        return array_keys($this->getLanguages());
    }

    public function getModeForLocale(string $locale): string
    {
        return $this->config['languages'][$locale]['mode'] ?? 'independent';
    }

    public function isValidLocale(string $locale): bool
    {
        return array_key_exists($locale, $this->getLanguages());
    }
}
