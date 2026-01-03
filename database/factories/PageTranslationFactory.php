<?php

namespace Database\Factories;

use App\Models\PageTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PageTranslationFactory extends Factory
{
    protected $model = PageTranslation::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);
        
        return [
            'locale' => 'en',
            'title' => $title,
            'slug' => Str::slug($title),
            'blocks' => [],
            'meta_title' => $title,
            'meta_description' => fake()->sentence(),
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
}
