<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_approved' => true]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_ensures_only_one_root_page_exists()
    {
        // 1. Create initial root page
        $root1 = Page::create(['is_root' => true, 'layout' => 'default']);
        $root1->translations()->create([
            'locale' => 'en',
            'title' => 'Root 1',
            'slug' => 'root-1',
            'is_published' => true
        ]);

        $this->assertTrue($root1->fresh()->is_root);

        // 2. Create another page and set it as root via Controller
        $response = $this->post(route('admin.pages.store'), [
            'title' => 'Root 2',
            'is_root' => '1',
            'locale' => 'en',
            'layout' => 'default',
        ]);

        $response->assertStatus(302);
        
        // 3. Verify first root is no longer root
        $this->assertFalse($root1->fresh()->is_root);
        
        // 4. Verify second page is now root
        $root2 = Page::where('is_root', true)->first();
        $this->assertNotNull($root2);
        $this->assertEquals($root2->id, Page::whereHas('translations', function($q) {
            $q->where('title', 'Root 2');
        })->first()->id);
    }

    /** @test */
    public function it_falls_back_to_title_slug_when_creating_new_translation_if_slug_missing()
    {
        // This tests the fix for the IntegrityConstraintViolation (slug cannot be null)
        $page = Page::create(['is_root' => false, 'layout' => 'default']);
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'English Title',
            'slug' => 'english-slug',
            'is_published' => true
        ]);

        // Try to update/create 'de' translation without providing a slug
        $response = $this->put(route('admin.pages.update', $page->id), [
            'locale' => 'de',
            'title' => 'German Title',
            'slug' => '', // Empty slug
            'is_published' => '1',
            'sitemap_priority' => 0.8,
            'sitemap_changefreq' => 'weekly',
        ]);

        $response->assertStatus(302);
        
        // Verify 'de' translation was created with a slug derived from title
        $translation = $page->fresh()->translation('de');
        $this->assertNotNull($translation);
        $this->assertEquals('german-title', $translation->slug);
    }

    /** @test */
    public function it_hides_root_checkbox_in_create_view_if_root_exists()
    {
        // Create a root page
        Page::create(['is_root' => true, 'layout' => 'default'])
            ->translations()->create(['locale' => 'en', 'title' => 'Home', 'slug' => 'home']);

        $response = $this->get(route('admin.pages.create'));
        
        $response->assertStatus(200);
        $response->assertDontSee('id="is_root"');
    }

    /** @test */
    public function it_shows_root_checkbox_in_edit_view_only_for_current_root_or_if_no_root_exists()
    {
        // Case 1: Root exists. Edit root page. Should see checkbox.
        $root = Page::create(['is_root' => true, 'layout' => 'default']);
        $root->translations()->create(['locale' => 'en', 'title' => 'Home', 'slug' => 'home']);

        $response = $this->get(route('admin.pages.edit', $root->id));
        $response->assertSee('id="is_root"', false);

        // Case 2: Root exists. Edit non-root page. Should NOT see checkbox.
        $other = Page::create(['is_root' => false, 'layout' => 'default']);
        $other->translations()->create(['locale' => 'en', 'title' => 'Other', 'slug' => 'other']);

        $response = $this->get(route('admin.pages.edit', $other->id));
        $response->assertDontSee('id="is_root"');
    }
}
