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
        Schema::table('system_logs', function (Blueprint $table) {
            $table->string('event')->nullable()->after('level'); // created, updated, deleted
            $table->nullableMorphs('auditable');
            $table->json('old_values')->nullable()->after('context');
            $table->json('new_values')->nullable()->after('old_values');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_logs', function (Blueprint $table) {
            $table->dropColumn(['event', 'auditable_type', 'auditable_id', 'old_values', 'new_values']);
        });
    }
};
