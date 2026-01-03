<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevisionDescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_description_for_simple_field_changes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $page = Page::create([
            'layout' => 'default',
        ]);

        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Old Title',
            'slug' => 'old-slug',
        ]);

        $page->translations()->where('locale', 'en')->first()->update(['title' => 'New Title']);

        // Get the latest revision (which should be the update)
        $revision = $page->revisions()->orderBy('id', 'desc')->first();
        $this->assertNotNull($revision);
        $this->assertStringContainsString('Title changed from "Old Title" to "New Title"', $revision->description);
    }

    public function test_it_generates_description_for_seo_field_changes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $page = Page::create([
            'layout' => 'default',
        ]);

        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Test Page',
            'slug' => 'test-page',
            'meta_title' => 'Old Meta Title',
        ]);

        $page->translations()->where('locale', 'en')->first()->update(['meta_title' => 'New Meta Title']);

        $revision = $page->revisions()->orderBy('id', 'desc')->first();
        $this->assertNotNull($revision);
        $this->assertStringContainsString('Title changed from "Old Meta Title" to "New Meta Title"', $revision->description);
    }

    public function test_it_generates_description_for_blocks_changes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $page = Page::create([
            'layout' => 'default',
        ]);

        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Test Page',
            'slug' => 'test-page',
            'blocks' => [],
        ]);

        // Clear existing revisions to isolate the blocks update
        $page->revisions()->delete();

        // Test block addition
        $page->translations()->where('locale', 'en')->first()->update(['blocks' => [
            ['type' => 'text', 'content' => 'hello']
        ]]);

        $revision = $page->revisions()->latest()->first();
        $this->assertNotNull($revision);
        $this->assertStringContainsString('Added #1 text block', $revision->description);

        // Test multiple blocks addition
        $page->revisions()->delete();
        $page->translations()->where('locale', 'en')->first()->update(['blocks' => [
            ['type' => 'text', 'content' => 'hello'],
            ['type' => 'image', 'src' => 'test.jpg'],
            ['type' => 'heading', 'text' => 'Title']
        ]]);

        $revision = $page->revisions()->latest()->first();
        $this->assertNotNull($revision);
        $this->assertStringContainsString('Added #2 image block', $revision->description);
        $this->assertStringContainsString('Added #3 heading block', $revision->description);

        // Test field update in existing block
        $page->revisions()->delete();
        $page->translations()->where('locale', 'en')->first()->update(['blocks' => [
            ['type' => 'text', 'content' => 'updated content'],
            ['type' => 'image', 'src' => 'test.jpg'],
            ['type' => 'heading', 'text' => 'Title']
        ]]);

        $revision = $page->revisions()->latest()->first();
        $this->assertNotNull($revision);
        $this->assertStringContainsString('Updated content in #1 text block', $revision->description);

        // Test block deletion
        $page->revisions()->delete();
        $page->translations()->where('locale', 'en')->first()->update(['blocks' => [
            ['type' => 'text', 'content' => 'updated content']
        ]]);

        $revision = $page->revisions()->latest()->first();
        $this->assertNotNull($revision);
        $this->assertStringContainsString('Deleted #2 image block', $revision->description);
        $this->assertStringContainsString('Deleted #3 heading block', $revision->description);
    }
}
