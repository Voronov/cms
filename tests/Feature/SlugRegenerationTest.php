<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Redirect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlugRegenerationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_approved' => true]);
        $this->actingAs($this->user);
    }

    public function test_it_can_regenerate_slug_and_optionally_create_redirect(): void
    {
        $page = Page::create([
            'layout' => 'default',
            'sitemap_include' => true,
            'sitemap_priority' => 0.8,
            'sitemap_changefreq' => 'weekly',
        ]);
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Old Title',
            'slug' => 'old-slug',
            'is_published' => true,
        ]);

        $response = $this->put(route('admin.pages.update', $page->id), [
            'locale' => 'en',
            'title' => 'New Title',
            'regenerate_slug' => '1',
            'create_redirect' => '1',
            'layout' => 'default',
            'sitemap_include' => '1',
            'sitemap_priority' => '0.8',
            'sitemap_changefreq' => 'weekly',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        
        $translation = \App\Models\PageTranslation::where('page_id', $page->id)
            ->where('locale', 'en')
            ->first();
            
        $this->assertEquals('new-title', $translation->slug);

        $this->assertDatabaseHas('redirects', [
            'from_url' => '/old-slug',
            'to_url' => '/new-title',
            'page_id' => $page->id,
        ]);
    }

    public function test_it_regenerates_slug_without_redirect_if_not_requested(): void
    {
        $page = Page::create([
            'layout' => 'default',
            'sitemap_include' => true,
            'sitemap_priority' => 0.8,
            'sitemap_changefreq' => 'weekly',
        ]);
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Old Title',
            'slug' => 'old-slug',
            'is_published' => true,
        ]);

        $response = $this->put(route('admin.pages.update', $page->id), [
            'locale' => 'en',
            'title' => 'New Title',
            'regenerate_slug' => '1',
            'create_redirect' => '0',
            'layout' => 'default',
            'sitemap_include' => '1',
            'sitemap_priority' => '0.8',
            'sitemap_changefreq' => 'weekly',
        ]);

        $response->assertSessionHasNoErrors();
        
        $translation = \App\Models\PageTranslation::where('page_id', $page->id)
            ->where('locale', 'en')
            ->first();

        $this->assertEquals('new-title', $translation->slug);
        $this->assertDatabaseMissing('redirects', ['from_url' => '/old-slug']);
    }
}
