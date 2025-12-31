<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Str;

class EntityDefinitionService
{
    protected array $definitions = [];
    protected string $entitiesPath;

    public function __construct()
    {
        $this->entitiesPath = resource_path('entities');
        $this->loadDefinitions();
    }

    protected function loadDefinitions(): void
    {
        if (!File::exists($this->entitiesPath)) {
            return;
        }

        $files = File::files($this->entitiesPath);
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'yaml') {
                $type = $file->getFilenameWithoutExtension();
                $definition = Yaml::parseFile($file->getPathname());
                $this->definitions[$type] = $definition;
            }
        }
    }

    public function getAll(): array
    {
        return $this->definitions;
    }

    public function get(string $type): ?array
    {
        return $this->definitions[$type] ?? null;
    }

    public function exists(string $type): bool
    {
        return isset($this->definitions[$type]);
    }

    public function getTypes(): array
    {
        return array_keys($this->definitions);
    }

    public function getName(string $type): ?string
    {
        return $this->definitions[$type]['name'] ?? null;
    }

    public function getPluralName(string $type): ?string
    {
        return $this->definitions[$type]['plural'] ?? $this->getName($type);
    }

    public function getSingularName(string $type): ?string
    {
        return $this->definitions[$type]['singular'] ?? $this->getName($type);
    }

    public function getIcon(string $type): string
    {
        return $this->definitions[$type]['icon'] ?? 'document';
    }

    public function getDescription(string $type): ?string
    {
        return $this->definitions[$type]['description'] ?? null;
    }

    public function getFields(string $type): array
    {
        return $this->definitions[$type]['fields'] ?? [];
    }

    public function getField(string $type, string $fieldName): ?array
    {
        $fields = $this->getFields($type);
        foreach ($fields as $field) {
            if ($field['name'] === $fieldName) {
                return $field;
            }
        }
        return null;
    }

    public function getListLayout(string $type): string
    {
        return $this->definitions[$type]['list_layout'] ?? 'grid';
    }

    public function getListColumns(string $type): int
    {
        return $this->definitions[$type]['list_columns'] ?? 3;
    }

    public function getTemplateMap(string $type): array
    {
        return $this->definitions[$type]['template_map'] ?? [];
    }

    public function getTemplate(string $type, string $layout): ?string
    {
        $templateMap = $this->getTemplateMap($type);
        return $templateMap[$layout] ?? null;
    }

    public function getDefaultPagination(string $type): int
    {
        return $this->definitions[$type]['default_pagination'] ?? 12;
    }

    public function getDetailLayout(string $type): ?string
    {
        return $this->definitions[$type]['detail_layout'] ?? null;
    }

    public function getSlugField(string $type): ?string
    {
        return $this->definitions[$type]['slug_field'] ?? null;
    }

    public function getSeoConfig(string $type): ?array
    {
        return $this->definitions[$type]['seo'] ?? null;
    }

    public function isSeoEnabled(string $type): bool
    {
        $seoConfig = $this->getSeoConfig($type);
        return $seoConfig['enabled'] ?? false;
    }

    public function getSeoTitleField(string $type): ?string
    {
        $seoConfig = $this->getSeoConfig($type);
        return $seoConfig['title_field'] ?? null;
    }

    public function getSeoDescriptionField(string $type): ?string
    {
        $seoConfig = $this->getSeoConfig($type);
        return $seoConfig['description_field'] ?? null;
    }

    public function getSeoImageField(string $type): ?string
    {
        $seoConfig = $this->getSeoConfig($type);
        return $seoConfig['image_field'] ?? null;
    }

    public function getCategories(string $type): array
    {
        return $this->definitions[$type]['categories'] ?? [];
    }

    public function getCategoryBySlug(string $type, string $slug): ?array
    {
        $categories = $this->getCategories($type);
        foreach ($categories as $category) {
            if ($category['slug'] === $slug) {
                return $category;
            }
        }
        return null;
    }

    public function hasCategories(string $type): bool
    {
        return !empty($this->getCategories($type));
    }

    public function generateSlug(string $type, array $content): ?string
    {
        $slugField = $this->getSlugField($type);
        if (!$slugField || !isset($content[$slugField])) {
            return null;
        }

        return Str::slug($content[$slugField]);
    }

    public function buildSeoData(string $type, array $content): array
    {
        if (!$this->isSeoEnabled($type)) {
            return [];
        }

        $seo = [];
        
        $titleField = $this->getSeoTitleField($type);
        if ($titleField && isset($content[$titleField])) {
            $seo['title'] = $content[$titleField];
        }

        $descriptionField = $this->getSeoDescriptionField($type);
        if ($descriptionField && isset($content[$descriptionField])) {
            $seo['description'] = $content[$descriptionField];
        }

        $imageField = $this->getSeoImageField($type);
        if ($imageField && isset($content[$imageField])) {
            $seo['image'] = $content[$imageField];
        }

        return $seo;
    }

    public function validateContent(string $type, array $content): array
    {
        $errors = [];
        $fields = $this->getFields($type);

        foreach ($fields as $field) {
            $fieldName = $field['name'];
            $isRequired = $field['required'] ?? false;
            $value = $content[$fieldName] ?? null;

            if ($isRequired && empty($value)) {
                $errors[$fieldName] = "The {$field['label']} field is required.";
            }
        }

        return $errors;
    }

    public function getDefaultValues(string $type): array
    {
        $defaults = [];
        $fields = $this->getFields($type);

        foreach ($fields as $field) {
            if (isset($field['default'])) {
                $default = $field['default'];
                if ($default === 'now' && $field['type'] === 'datetime') {
                    $defaults[$field['name']] = now()->toDateTimeString();
                } else {
                    $defaults[$field['name']] = $default;
                }
            }
        }

        return $defaults;
    }

    public function createDefinition(string $type, array $data): bool
    {
        $filePath = $this->entitiesPath . '/' . $type . '.yaml';
        
        if (File::exists($filePath)) {
            return false;
        }

        $yaml = Yaml::dump($data, 4, 2);
        File::put($filePath, $yaml);
        
        $this->definitions[$type] = $data;
        
        return true;
    }

    public function updateDefinition(string $type, array $data): bool
    {
        $filePath = $this->entitiesPath . '/' . $type . '.yaml';
        
        if (!File::exists($filePath)) {
            return false;
        }

        $yaml = Yaml::dump($data, 4, 2);
        File::put($filePath, $yaml);
        
        $this->definitions[$type] = $data;
        
        return true;
    }

    public function deleteDefinition(string $type): bool
    {
        $filePath = $this->entitiesPath . '/' . $type . '.yaml';
        
        if (!File::exists($filePath)) {
            return false;
        }

        File::delete($filePath);
        unset($this->definitions[$type]);
        
        return true;
    }
}
