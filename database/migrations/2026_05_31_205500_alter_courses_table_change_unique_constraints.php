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
        Schema::table('courses', function (Blueprint $table) {
            // Drop global unique indexes
            $table->dropUnique('courses_slug_unique');
            $table->dropUnique('courses_code_unique');

            // Add composite unique indexes (unique per category)
            $table->unique(['category_id', 'slug']);
            $table->unique(['category_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Drop composite unique indexes
            $table->dropUnique(['category_id', 'slug']);
            $table->dropUnique(['category_id', 'code']);

            // Re-add global unique indexes
            $table->unique('slug');
            $table->unique('code');
        });
    }
};
