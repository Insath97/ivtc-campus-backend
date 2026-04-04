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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_code')->unique();
            $table->foreignId('pathway_id')->constrained('pathways')->onDelete('cascade');

            // Polymorphic Program Selection
            $table->unsignedBigInteger('program_id');
            $table->enum('program_type', ['course', 'program']);

            // Student Personal Details
            $table->string('full_name');
            $table->string('nic');
            $table->date('dob');
            $table->string('gender');
            $table->string('phone');
            $table->string('email');
            $table->string('district');
            $table->string('city');

            // Dynamic Fields (nullable depending on pathway)
            $table->string('school_name')->nullable();
            $table->string('occupation')->nullable();

            // Status and Remarks
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
