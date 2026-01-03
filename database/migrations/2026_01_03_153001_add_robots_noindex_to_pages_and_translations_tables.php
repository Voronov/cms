<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('robots_noindex')->default(false)->after('sitemap_changefreq');
        });

        Schema::table('page_translations', function (Blueprint $table) {
            $table->boolean('robots_noindex')->default(false)->after('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('robots_noindex');
        });

        Schema::table('page_translations', function (Blueprint $table) {
            $table->dropColumn('robots_noindex');
        });
    }
};
