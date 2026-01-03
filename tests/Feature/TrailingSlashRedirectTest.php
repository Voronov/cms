<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrailingSlashRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = User::factory()->create([
            'is_approved' => true,
            'email_verified_at' => now(),
        ]);
        
        $this->actingAs($user);
    }

    public function test_root_url_shows_home_page(): void
    {
        $page = Page::factory()->create([
            'is_root' => true,
            'is_published' => true,
        ]);

        PageTranslation::factory()->create([
            'page_id' => $page->id,
            'locale' => 'en',
            'title' => 'Home',
            'slug' => 'home',
            'is_published' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('page');
        $response->assertViewHas('page', function ($viewPage) use ($page) {
            return $viewPage->id === $page->id;
        });
    }

    public function test_page_url_without_trailing_slash_works_correctly(): void
    {
        $page = Page::factory()->create([
            'is_published' => true,
        ]);

        PageTranslation::factory()->create([
            'page_id' => $page->id,
            'locale' => 'en',
            'title' => 'About',
            'slug' => 'about',
            'is_published' => true,
        ]);

        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertViewIs('page');
        $response->assertViewHas('page', function ($viewPage) use ($page) {
            return $viewPage->id === $page->id;
        });
    }

    public function test_deeply_nested_page_url_works(): void
    {
        $level1 = Page::factory()->create(['is_published' => true]);
        PageTranslation::factory()->create([
            'page_id' => $level1->id,
            'locale' => 'en',
            'slug' => 'products',
            'is_published' => true,
        ]);

        $level2 = Page::factory()->create([
            'parent_id' => $level1->id,
            'is_published' => true,
        ]);
        PageTranslation::factory()->create([
            'page_id' => $level2->id,
            'locale' => 'en',
            'slug' => 'electronics',
            'is_published' => true,
        ]);

        $level3 = Page::factory()->create([
            'parent_id' => $level2->id,
            'is_published' => true,
        ]);
        PageTranslation::factory()->create([
            'page_id' => $level3->id,
            'locale' => 'en',
            'slug' => 'laptops',
            'is_published' => true,
        ]);

        $response = $this->get('/products/electronics/laptops');

        $response->assertStatus(200);
        $response->assertViewIs('page');
        $response->assertViewHas('page', function ($viewPage) use ($level3) {
            return $viewPage->id === $level3->id;
        });
    }

    public function test_admin_dashboard_works(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(200);
    }

    public function test_admin_pages_index_works(): void
    {
        $response = $this->get('/admin/pages');

        $response->assertStatus(200);
    }

    public function test_unpublished_page_returns_404(): void
    {
        $page = Page::factory()->create([
            'is_published' => false,
        ]);

        PageTranslation::factory()->create([
            'page_id' => $page->id,
            'locale' => 'en',
            'title' => 'Hidden',
            'slug' => 'hidden',
            'is_published' => true,
        ]);

        $response = $this->get('/hidden');

        $response->assertStatus(404);
    }

    public function test_nonexistent_page_returns_404(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response->assertStatus(404);
    }
}
