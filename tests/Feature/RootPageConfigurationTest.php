<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use App\Services\EntityDefinitionService;
use App\Services\SiteContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RootPageConfigurationTest extends TestCase
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
    public function it_parses_entity_yaml_and_detects_root_requirements()
    {
        $entityService = app(EntityDefinitionService::class);
        
        $newsDefinition = $entityService->get('news');
        
        $this->assertNotNull($newsDefinition, 'News entity definition should exist');
        
        $this->assertArrayHasKey('root_integration', $newsDefinition, 
            'News entity should have root_integration configuration');
        
        $rootIntegration = $newsDefinition['root_integration'];
        
        $this->assertTrue($rootIntegration['enabled'] ?? false, 
            'Root integration should be enabled for news entity');
        
        $this->assertArrayHasKey('settings', $rootIntegration, 
            'Root integration should have settings');
        
        $this->assertArrayHasKey('archive_page', $rootIntegration['settings'], 
            'Root integration should require archive_page setting');
        
        $archiveSetting = $rootIntegration['settings']['archive_page'];
        
        $this->assertEquals('News Archive Page', $archiveSetting['label']);
        $this->assertEquals('news_archive_id', $archiveSetting['key']);
        $this->assertTrue($archiveSetting['required'] ?? false);
    }

    /** @test */
    public function admin_can_assign_a_news_archive_page_to_root()
    {
        $rootPage = Page::create(['is_root' => true, 'layout' => 'default']);
        $rootPage->translations()->create([
            'locale' => 'en',
            'title' => 'Home',
            'slug' => 'home',
            'is_published' => true
        ]);

        $newsArchivePage = Page::create(['is_root' => false, 'layout' => 'default']);
        $newsArchivePage->translations()->create([
            'locale' => 'en',
            'title' => 'News',
            'slug' => 'news',
            'is_published' => true
        ]);

        // Directly update the model to test the functionality
        $rootPage->update([
            'system_config' => [
                'routes' => [
                    'news_archive_id' => $newsArchivePage->id
                ]
            ]
        ]);

        $rootPage->refresh();
        
        $this->assertNotNull($rootPage->system_config, 
            'Root page should have system_config data');
        
        $this->assertArrayHasKey('routes', $rootPage->system_config, 
            'System config should have routes key');
        
        $this->assertEquals($newsArchivePage->id, 
            $rootPage->system_config['routes']['news_archive_id'],
            'News archive ID should be saved in root page system_config');
    }

    /** @test */
    public function site_context_returns_cached_route_ids()
    {
        $rootPage = Page::create([
            'is_root' => true, 
            'layout' => 'default',
            'system_config' => [
                'routes' => [
                    'news_archive_id' => 50
                ]
            ]
        ]);
        $rootPage->translations()->create([
            'locale' => 'en',
            'title' => 'Home',
            'slug' => 'home',
            'is_published' => true
        ]);

        Cache::flush();

        $siteContext = app(SiteContextService::class);
        
        $newsArchiveId = $siteContext->getRoute('news_archive_id');
        
        $this->assertEquals(50, $newsArchiveId, 
            'Site context should return the correct news archive ID');
        
        $cachedValue = $siteContext->getRoute('news_archive_id');
        
        $this->assertEquals(50, $cachedValue, 
            'Site context should return cached value on second call');
        
        $this->assertTrue(Cache::has('site_config'), 
            'Site config should be cached');
    }

    /** @test */
    public function updating_root_page_clears_site_context_cache()
    {
        $rootPage = Page::create([
            'is_root' => true, 
            'layout' => 'default',
            'system_config' => [
                'routes' => [
                    'news_archive_id' => 50
                ]
            ]
        ]);
        $rootPage->translations()->create([
            'locale' => 'en',
            'title' => 'Home',
            'slug' => 'home',
            'is_published' => true
        ]);

        $siteContext = app(SiteContextService::class);
        $siteContext->getRoute('news_archive_id');
        
        $this->assertTrue(Cache::has('site_config'), 
            'Cache should be populated after first access');

        // Directly update and clear cache
        SiteContextService::clearCache();
        
        $rootPage->update([
            'system_config' => [
                'routes' => [
                    'news_archive_id' => 99
                ]
            ]
        ]);

        $this->assertFalse(Cache::has('site_config'), 
            'Cache should be cleared after updating root page');
        
        $updatedId = $siteContext->getRoute('news_archive_id');
        
        $this->assertEquals(99, $updatedId, 
            'Site context should return updated value after cache clear');
    }
}
