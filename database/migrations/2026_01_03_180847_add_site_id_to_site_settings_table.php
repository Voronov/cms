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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->constrained('pages')->onDelete('cascade');
            $table->dropUnique(['key']);
            $table->unique(['site_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropUnique(['site_id', 'key']);
            $table->dropForeign(['site_id']);
            $table->dropColumn('site_id');
            $table->unique(['key']);
        });
    }
};
