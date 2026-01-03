<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('page_type')->default('regular')->after('is_root');
            $table->integer('order')->default(0)->after('parent_id');
            $table->index(['parent_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex(['parent_id', 'order']);
            $table->dropColumn(['page_type', 'order']);
        });
    }
};
