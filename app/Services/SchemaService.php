<?php

namespace App\Services;

use App\Models\Entity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

class SchemaService
{
    public function generate(Entity $entity, array $breadcrumbs = []): string
    {
        $schemas = [];

        // 1. BreadcrumbList Schema
        if (!empty($breadcrumbs)) {
            $schemas[] = $this->generateBreadcrumbSchema($breadcrumbs);
        }

        // 2. Content Specific Schema (Article/News)
        if (in_array($entity->type, ['article', 'news'])) {
            $schemas[] = $this->generateArticleSchema($entity);
        }

        if (empty($schemas)) {
            return '';
        }

        return collect($schemas)
            ->map(fn($schema) => '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>')
            ->implode("\n");
    }

    public function renderBreadcrumbs(array $breadcrumbs = []): string
    {
        if (empty($breadcrumbs)) {
            return '';
        }

        $html = '<nav class="flex mb-8" aria-label="Breadcrumb">';
        $html .= '<ol class="inline-flex items-center space-x-1 md:space-x-3">';
        
        // Home
        $html .= '<li class="inline-flex items-center">';
        $html .= '<a href="' . url('/') . '" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-white">';
        $html .= '<svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>';
        $html .= 'Home';
        $html .= '</a></li>';

        $total = count($breadcrumbs);
        $i = 0;
        foreach ($breadcrumbs as $name => $url) {
            $i++;
            $isLast = $i === $total;
            
            $html .= '<li><div class="flex items-center">';
            $html .= '<svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>';
            
            if ($isLast) {
                $html .= '<span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">' . e($name) . '</span>';
            } else {
                $html .= '<a href="' . $url . '" class="ml-1 text-sm font-medium text-gray-700 hover:text-indigo-600 md:ml-2 dark:text-gray-400 dark:hover:text-white">' . e($name) . '</a>';
            }
            
            $html .= '</div></li>';
        }

        $html .= '</ol></nav>';

        return $html;
    }

    protected function generateBreadcrumbSchema(array $breadcrumbs): array
    {
        $items = [];
        $i = 1;

        // Add Home
        $items[] = [
            '@type' => 'ListItem',
            'position' => $i++,
            'name' => 'Home',
            'item' => url('/')
        ];

        foreach ($breadcrumbs as $name => $url) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $i++,
                'name' => $name,
                'item' => $url
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items
        ];
    }

    protected function generateArticleSchema(Entity $entity): array
    {
        $content = $entity->content;
        $seo = $entity->seo;

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $seo['title'] ?? $content['title'] ?? '',
            'description' => $seo['description'] ?? $content['summary'] ?? $content['excerpt'] ?? '',
            'image' => $this->getAbsoluteImageUrl($seo['image'] ?? $content['cover_image'] ?? $content['featured_image'] ?? null),
            'datePublished' => $entity->published_at?->toIso8601String() ?? $entity->created_at->toIso8601String(),
            'dateModified' => $entity->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Organization',
                'name' => config('app.name')
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => url('/logo.png') // Assume a default logo exists or can be configured
                ]
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => Request::url()
            ]
        ];
    }

    protected function getAbsoluteImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return url($path);
    }
}
