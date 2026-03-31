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
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->date('starting_date');
            $table->date('ending_date');
            $table->string('entrol_number')->unique()->nullable();
            $table->string('course_code')->unique()->nullable();
            $table->string('verification_code')->unique();
            $table->string('certificate_number')->unique();
            $table->string('nic')->unique();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
