<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_approved' => true]);
        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        // Clean up test YAML files
        $testFiles = [
            resource_path('sites/test-auto-detect.yaml'),
            resource_path('sites/test-site-new.yaml'),
        ];

        foreach ($testFiles as $file) {
            if (File::exists($file)) {
                File::delete($file);
            }
        }

        parent::tearDown();
    }

    /** @test */
    public function it_auto_detects_site_key_from_yaml_filename()
    {
        // Temporarily rename existing YAML files to avoid interference
        $existingFiles = File::files(resource_path('sites'));
        $renamedFiles = [];
        foreach ($existingFiles as $file) {
            if ($file->getExtension() === 'yaml') {
                $oldPath = $file->getPathname();
                $newPath = $oldPath . '.bak';
                File::move($oldPath, $newPath);
                $renamedFiles[] = ['old' => $oldPath, 'new' => $newPath];
            }
        }

        try {
            // Create a root page without site_key
            $rootPage = Page::create([
                'is_root' => true,
                'layout' => 'default',
                'site_key' => null,
            ]);
            $rootPage->translations()->create([
                'locale' => 'en',
                'title' => 'Test Site',
                'slug' => 'home',
                'is_published' => true,
            ]);

            // Create a test YAML file with site_id to ensure proper matching
            $yamlContent = "site_id: {$rootPage->id}\n" .
    "site_key: test-auto-detect\n" .
    "name: Test Auto Detect Site\n\n" .
    "domains:\n" .
    "  - https://test-auto.local\n\n" .
    "languages:\n" .
    "  en:\n" .
    "    name: English\n" .
    "    native: English\n" .
    "    default: true\n" .
    "    mode: standalone\n";

            File::ensureDirectoryExists(resource_path('sites'));
            File::put(resource_path('sites/test-auto-detect.yaml'), $yamlContent);

            // Access settings page
            $response = $this->get(route('admin.settings.index', ['site_id' => $rootPage->id]));

            $response->assertStatus(200);

            // Verify site_key was auto-detected and saved
            $rootPage->refresh();
            $this->assertEquals('test-auto-detect', $rootPage->site_key);

            // Verify domains are loaded
            $response->assertSee('https://test-auto.local');
        } finally {
            // Restore renamed files
            foreach ($renamedFiles as $file) {
                if (File::exists($file['new'])) {
                    File::move($file['new'], $file['old']);
                }
            }
        }
    }

    /** @test */
    public function it_displays_domains_when_site_key_is_set()
    {
        // Create a root page with site_key
        $rootPage = Page::create([
            'is_root' => true,
            'layout' => 'default',
            'site_key' => 'test-site-new',
        ]);
        $rootPage->translations()->create([
            'locale' => 'en',
            'title' => 'Test Site',
            'slug' => 'home',
            'is_published' => true,
        ]);

        // Create a test YAML file
        $yamlContent = <<<YAML
site_key: test-site-new
name: Test Site New

domains:
  - https://example.com
  - https://www.example.com

primary_domain: https://example.com

languages:
  en:
    name: English
    default: true
    mode: standalone
YAML;

        File::ensureDirectoryExists(resource_path('sites'));
        File::put(resource_path('sites/test-site-new.yaml'), $yamlContent);

        // Access settings page
        $response = $this->get(route('admin.settings.index', ['site_id' => $rootPage->id]));

        $response->assertStatus(200);
        $response->assertSee('https://example.com');
        $response->assertSee('https://www.example.com');
        $response->assertSee('Primary');
    }

    /** @test */
    public function it_handles_languages_without_native_key()
    {
        // Create a root page with site_key
        $rootPage = Page::create([
            'is_root' => true,
            'layout' => 'default',
            'site_key' => 'test-site-new',
        ]);
        $rootPage->translations()->create([
            'locale' => 'en',
            'title' => 'Test Site',
            'slug' => 'home',
            'is_published' => true,
        ]);

        // Create a test YAML file with languages missing 'native' key
        $yamlContent = <<<YAML
site_key: test-site-new
name: Test Site New

domains:
  - https://example.com

languages:
  en:
    name: English
    default: true
    mode: standalone
  es:
    name: Spanish
    mode: copy
YAML;

        File::ensureDirectoryExists(resource_path('sites'));
        File::put(resource_path('sites/test-site-new.yaml'), $yamlContent);

        // Access settings page - should not throw error
        $response = $this->get(route('admin.settings.index', ['site_id' => $rootPage->id]));

        $response->assertStatus(200);
        $response->assertSee('English');
        $response->assertSee('Spanish');
        $response->assertDontSee('Undefined array key');
    }

    /** @test */
    public function it_handles_languages_with_native_key()
    {
        // Create a root page with site_key
        $rootPage = Page::create([
            'is_root' => true,
            'layout' => 'default',
            'site_key' => 'test-site-new',
        ]);
        $rootPage->translations()->create([
            'locale' => 'en',
            'title' => 'Test Site',
            'slug' => 'home',
            'is_published' => true,
        ]);

        // Create a test YAML file with languages including 'native' key
        $yamlContent = <<<YAML
site_key: test-site-new
name: Test Site New

domains:
  - https://example.com

languages:
  en:
    name: English
    native: English
    default: true
    mode: standalone
  es:
    name: Spanish
    native: Español
    mode: copy
YAML;

        File::ensureDirectoryExists(resource_path('sites'));
        File::put(resource_path('sites/test-site-new.yaml'), $yamlContent);

        // Access settings page
        $response = $this->get(route('admin.settings.index', ['site_id' => $rootPage->id]));

        $response->assertStatus(200);
        $response->assertSee('English (English)');
        $response->assertSee('Spanish (Español)');
    }

    /** @test */
    public function it_shows_warning_when_no_matching_yaml_file_exists()
    {
        // Create a root page with a site_key that has no matching YAML file
        $rootPage = Page::create([
            'is_root' => true,
            'layout' => 'default',
            'site_key' => 'non-existent-site-key-12345',
        ]);
        $rootPage->translations()->create([
            'locale' => 'en',
            'title' => 'Test Site',
            'slug' => 'home',
            'is_published' => true,
        ]);

        // Access settings page (no matching YAML file exists)
        $response = $this->get(route('admin.settings.index', ['site_id' => $rootPage->id]));

        $response->assertStatus(200);
        $response->assertSee('No domains configured');
    }

    /** @test */
    public function it_updates_site_key_and_settings()
    {
        // Create a root page
        $rootPage = Page::create([
            'is_root' => true,
            'layout' => 'default',
            'site_key' => 'old-key',
        ]);
        $rootPage->translations()->create([
            'locale' => 'en',
            'title' => 'Test Site',
            'slug' => 'home',
            'is_published' => true,
        ]);

        // Update settings
        $response = $this->put(route('admin.settings.update'), [
            'site_id' => $rootPage->id,
            'site_key' => 'new-key',
            '404_page_id' => null,
            'entity_settings' => [],
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.settings.index', ['site_id' => $rootPage->id]));

        // Verify site_key was updated
        $rootPage->refresh();
        $this->assertEquals('new-key', $rootPage->site_key);
    }

    /** @test */
    public function it_prevents_duplicate_site_keys()
    {
        // Create two root pages
        $rootPage1 = Page::create([
            'is_root' => true,
            'layout' => 'default',
            'site_key' => 'site-one',
        ]);
        $rootPage1->translations()->create([
            'locale' => 'en',
            'title' => 'Site One',
            'slug' => 'home',
            'is_published' => true,
        ]);

        // Manually set is_root to false for first page to allow second root
        $rootPage1->update(['is_root' => false]);

        $rootPage2 = Page::create([
            'is_root' => true,
            'layout' => 'default',
            'site_key' => 'site-two',
        ]);
        $rootPage2->translations()->create([
            'locale' => 'en',
            'title' => 'Site Two',
            'slug' => 'home-2',
            'is_published' => true,
        ]);

        // Restore first page as root for testing
        $rootPage1->update(['is_root' => true]);
        $rootPage2->update(['is_root' => true]);

        // Try to update rootPage2 with rootPage1's site_key
        $response = $this->put(route('admin.settings.update'), [
            'site_id' => $rootPage2->id,
            'site_key' => 'site-one',
            '404_page_id' => null,
            'entity_settings' => [],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('site_key');
    }

    /** @test */
    public function it_stores_404_page_setting()
    {
        // Create a root page
        $rootPage = Page::create([
            'is_root' => true,
            'layout' => 'default',
            'site_key' => 'test-site',
        ]);
        $rootPage->translations()->create([
            'locale' => 'en',
            'title' => 'Test Site',
            'slug' => 'home',
            'is_published' => true,
        ]);

        // Create a 404 page
        $notFoundPage = Page::create([
            'is_root' => false,
            'layout' => 'default',
            'page_type' => '404',
        ]);
        $notFoundPage->translations()->create([
            'locale' => 'en',
            'title' => '404 Not Found',
            'slug' => '404',
            'is_published' => true,
        ]);

        // Update settings with 404 page
        $response = $this->put(route('admin.settings.update'), [
            'site_id' => $rootPage->id,
            'site_key' => 'test-site',
            '404_page_id' => $notFoundPage->id,
            'entity_settings' => [],
        ]);

        $response->assertStatus(302);

        // Verify 404 page was saved
        $saved404PageId = SiteSetting::get($rootPage->id, '404_page_id');
        $this->assertEquals($notFoundPage->id, $saved404PageId);
    }

    /** @test */
    public function it_stores_entity_settings()
    {
        // Create a root page
        $rootPage = Page::create([
            'is_root' => true,
            'layout' => 'default',
            'site_key' => 'test-site',
        ]);
        $rootPage->translations()->create([
            'locale' => 'en',
            'title' => 'Test Site',
            'slug' => 'home',
            'is_published' => true,
        ]);

        // Create an archive page
        $archivePage = Page::create([
            'is_root' => false,
            'layout' => 'default',
        ]);
        $archivePage->translations()->create([
            'locale' => 'en',
            'title' => 'News Archive',
            'slug' => 'news',
            'is_published' => true,
        ]);

        // Update settings with entity settings
        $response = $this->put(route('admin.settings.update'), [
            'site_id' => $rootPage->id,
            'site_key' => 'test-site',
            '404_page_id' => null,
            'entity_settings' => [
                'news' => [
                    'archive_page' => $archivePage->id,
                ],
            ],
        ]);

        $response->assertStatus(302);

        // Verify entity settings were saved
        $savedEntitySettings = SiteSetting::get($rootPage->id, 'entity_settings');
        $this->assertIsArray($savedEntitySettings);
        $this->assertEquals($archivePage->id, $savedEntitySettings['news']['archive_page']);
    }

    /** @test */
    public function it_loads_all_root_pages_for_site_selection()
    {
        // Create multiple root pages
        $rootPage1 = Page::create([
            'is_root' => true,
            'layout' => 'default',
            'site_key' => 'site-one',
        ]);
        $rootPage1->translations()->create([
            'locale' => 'en',
            'title' => 'Site One',
            'slug' => 'home-1',
            'is_published' => true,
        ]);

        // Manually set first as non-root to allow second root
        $rootPage1->update(['is_root' => false]);

        $rootPage2 = Page::create([
            'is_root' => true,
            'layout' => 'default',
            'site_key' => 'site-two',
        ]);
        $rootPage2->translations()->create([
            'locale' => 'en',
            'title' => 'Site Two',
            'slug' => 'home-2',
            'is_published' => true,
        ]);

        // Restore both as root
        $rootPage1->update(['is_root' => true]);

        // Access settings page
        $response = $this->get(route('admin.settings.index', ['site_id' => $rootPage1->id]));

        $response->assertStatus(200);
        $response->assertSee('Site One');
        $response->assertSee('Site Two');
        $response->assertSee('site_selector');
    }

    /** @test */
    public function it_detects_domain_conflicts_between_sites()
    {
        // Create first root page with YAML
        $rootPage1 = Page::create([
            'is_root' => true,
            'layout' => 'default',
            'site_key' => 'site-conflict-1',
        ]);
        $rootPage1->translations()->create([
            'locale' => 'en',
            'title' => 'Site One',
            'slug' => 'home-1',
            'is_published' => true,
        ]);

        $yaml1Content = <<<YAML
site_key: site-conflict-1
name: Site One

domains:
  - https://shared-domain.com
  - https://site1-unique.com

languages:
  en:
    name: English
    default: true
    mode: standalone
YAML;

        File::ensureDirectoryExists(resource_path('sites'));
        File::put(resource_path('sites/site-conflict-1.yaml'), $yaml1Content);

        // Manually set first as non-root to allow second root
        $rootPage1->update(['is_root' => false]);

        // Create second root page with conflicting domain
        $rootPage2 = Page::create([
            'is_root' => true,
            'layout' => 'default',
            'site_key' => 'site-conflict-2',
        ]);
        $rootPage2->translations()->create([
            'locale' => 'en',
            'title' => 'Site Two',
            'slug' => 'home-2',
            'is_published' => true,
        ]);

        $yaml2Content = <<<YAML
site_key: site-conflict-2
name: Site Two

domains:
  - https://shared-domain.com
  - https://site2-unique.com

languages:
  en:
    name: English
    default: true
    mode: standalone
YAML;

        File::put(resource_path('sites/site-conflict-2.yaml'), $yaml2Content);

        // Restore both as root
        $rootPage1->update(['is_root' => true]);

        // Access settings for site 2
        $response = $this->get(route('admin.settings.index', ['site_id' => $rootPage2->id]));

        $response->assertStatus(200);
        $response->assertSee('https://shared-domain.com');
        $response->assertSee('Conflict');
        $response->assertSee('Site One');

        // Clean up
        File::delete(resource_path('sites/site-conflict-1.yaml'));
        File::delete(resource_path('sites/site-conflict-2.yaml'));
    }

    /** @test */
    public function it_defaults_to_first_root_page_when_no_site_id_provided()
    {
        // Create a root page
        $rootPage = Page::create([
            'is_root' => true,
            'layout' => 'default',
            'site_key' => 'default-site',
        ]);
        $rootPage->translations()->create([
            'locale' => 'en',
            'title' => 'Default Site',
            'slug' => 'home',
            'is_published' => true,
        ]);

        // Access settings without site_id parameter
        $response = $this->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Default Site');
    }

    /** @test */
    public function it_shows_no_conflict_for_unique_domains()
    {
        // Create root page with unique domains
        $rootPage = Page::create([
            'is_root' => true,
            'layout' => 'default',
            'site_key' => 'unique-site',
        ]);
        $rootPage->translations()->create([
            'locale' => 'en',
            'title' => 'Unique Site',
            'slug' => 'home',
            'is_published' => true,
        ]);

        $yamlContent = <<<YAML
site_key: unique-site
name: Unique Site

domains:
  - https://unique-domain-12345.com

languages:
  en:
    name: English
    default: true
    mode: standalone
YAML;

        File::ensureDirectoryExists(resource_path('sites'));
        File::put(resource_path('sites/unique-site.yaml'), $yamlContent);

        // Access settings page
        $response = $this->get(route('admin.settings.index', ['site_id' => $rootPage->id]));

        $response->assertStatus(200);
        $response->assertSee('https://unique-domain-12345.com');
        $response->assertDontSee('Conflict');

        // Clean up
        File::delete(resource_path('sites/unique-site.yaml'));
    }
}
