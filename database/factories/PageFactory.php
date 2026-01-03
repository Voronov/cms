<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        return [
            'is_root' => false,
            'layout' => 'default',
            'is_published' => true,
            'parent_id' => null,
            'sitemap_include' => true,
            'sitemap_priority' => 0.5,
            'sitemap_changefreq' => 'weekly',
            'blocks' => [],
            'robots_noindex' => false,
        ];
    }

    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_root' => true,
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
}
