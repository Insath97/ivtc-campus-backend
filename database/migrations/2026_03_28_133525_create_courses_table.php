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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique();
            $table->integer('duration');
            $table->enum('duration_unit', ['month', 'year']);
            $table->enum('level', ['Beginner', 'Intermediate', 'Advanced', 'Professional']);
            $table->enum('medium', ['English', 'Sinhala', 'Tamil']);
            $table->text('short_description');
            $table->longText('full_description');
            $table->boolean('show_in_registration')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_new')->default(true);
            $table->string('primary_image')->nullable();
            $table->longText('fees_structure')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
