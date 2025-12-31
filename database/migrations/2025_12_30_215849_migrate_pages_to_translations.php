<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pages = DB::table('pages')->get();
        foreach ($pages as $page) {
            DB::table('page_translations')->insert([
                'page_id' => $page->id,
                'locale' => 'en',
                'title' => $page->title,
                'slug' => $page->slug,
                'blocks' => $page->blocks,
                'meta_title' => $page->meta_title,
                'meta_description' => $page->meta_description,
                'og_title' => $page->og_title,
                'og_description' => $page->og_description,
                'og_image' => $page->og_image,
                'is_published' => $page->is_published,
                'created_at' => $page->created_at,
                'updated_at' => $page->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('page_translations')->where('locale', 'en')->delete();
    }
};
