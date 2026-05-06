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
        Schema::create('lecturers', function (Blueprint $header) {
            $header->id();
            $header->string('name');
            $header->string('slug')->unique();
            $header->string('email')->unique();
            $header->string('phone')->nullable();
            $header->string('specialization')->nullable();
            $header->text('bio')->nullable();
            $header->string('image')->nullable();
            $header->string('linkedin_url')->nullable();
            $header->string('facebook_url')->nullable();
            $header->string('twitter_url')->nullable();
            $header->string('website_url')->nullable();
            $header->date('join_date')->nullable();
            $header->boolean('is_active')->default(true);
            $header->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $header->softDeletes();
            $header->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturers');
    }
};
