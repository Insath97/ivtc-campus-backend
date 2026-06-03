<?php

use App\Models\Registration;
use App\Models\Pathway;
use App\Models\Course;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed permissions
    $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
    
    // Create a super admin user
    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    
    // Generate JWT token
    $this->token = auth('api')->login($this->user);
});

it('can retrieve registrations listing including trashed', function () {
    $category = Category::create(['name' => 'Tech', 'slug' => 'tech', 'description' => 'Tech category']);
    $pathway = Pathway::create(['name' => 'IT', 'slug' => 'it', 'description' => 'IT pathway']);
    $course = Course::create([
        'category_id' => $category->id,
        'name' => 'PHP Course', 
        'code' => 'PHP101', 
        'slug' => 'php-course', 
        'duration' => 6,
        'duration_unit' => 'month',
        'level' => 'Beginner',
        'medium' => 'English',
        'short_description' => 'Short desc',
        'full_description' => 'Full desc',
        'is_active' => true
    ]);

    // Create a registration
    $registration = Registration::create([
        'pathway_id' => $pathway->id,
        'program_id' => $course->id,
        'program_type' => 'course',
        'full_name' => 'John Doe',
        'nic' => '123456789V',
        'dob' => '2000-01-01',
        'gender' => 'Male',
        'phone' => '0771234567',
        'email' => 'john@example.com',
        'district' => 'Colombo',
        'city' => 'Colombo'
    ]);

    // Soft delete registration
    $registration->delete();

    // Query registrations without trashed
    $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
        ->getJson('/api/v1/registrations');

    $response->assertStatus(200);
    $response->assertJsonCount(0, 'data.data');

    // Query registrations with trashed
    $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
        ->getJson('/api/v1/registrations?trashed=true');

    $response->assertStatus(200);
    $response->assertJsonCount(1, 'data.data');
    $response->assertJsonPath('data.data.0.id', $registration->id);
});

it('can restore a soft-deleted registration', function () {
    $category = Category::create(['name' => 'Tech', 'slug' => 'tech', 'description' => 'Tech category']);
    $pathway = Pathway::create(['name' => 'IT', 'slug' => 'it', 'description' => 'IT pathway']);
    $course = Course::create([
        'category_id' => $category->id,
        'name' => 'PHP Course', 
        'code' => 'PHP101', 
        'slug' => 'php-course', 
        'duration' => 6,
        'duration_unit' => 'month',
        'level' => 'Beginner',
        'medium' => 'English',
        'short_description' => 'Short desc',
        'full_description' => 'Full desc',
        'is_active' => true
    ]);

    $registration = Registration::create([
        'pathway_id' => $pathway->id,
        'program_id' => $course->id,
        'program_type' => 'course',
        'full_name' => 'John Doe',
        'nic' => '123456789V',
        'dob' => '2000-01-01',
        'gender' => 'Male',
        'phone' => '0771234567',
        'email' => 'john@example.com',
        'district' => 'Colombo',
        'city' => 'Colombo'
    ]);

    $registration->delete();

    $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
        ->patchJson("/api/v1/registrations/{$registration->id}/restore");

    $response->assertStatus(200);
    $response->assertJsonPath('status', 'success');
    
    $this->assertDatabaseHas('registrations', [
        'id' => $registration->id,
        'deleted_at' => null
    ]);
});

it('can force delete a registration', function () {
    $category = Category::create(['name' => 'Tech', 'slug' => 'tech', 'description' => 'Tech category']);
    $pathway = Pathway::create(['name' => 'IT', 'slug' => 'it', 'description' => 'IT pathway']);
    $course = Course::create([
        'category_id' => $category->id,
        'name' => 'PHP Course', 
        'code' => 'PHP101', 
        'slug' => 'php-course', 
        'duration' => 6,
        'duration_unit' => 'month',
        'level' => 'Beginner',
        'medium' => 'English',
        'short_description' => 'Short desc',
        'full_description' => 'Full desc',
        'is_active' => true
    ]);

    $registration = Registration::create([
        'pathway_id' => $pathway->id,
        'program_id' => $course->id,
        'program_type' => 'course',
        'full_name' => 'John Doe',
        'nic' => '123456789V',
        'dob' => '2000-01-01',
        'gender' => 'Male',
        'phone' => '0771234567',
        'email' => 'john@example.com',
        'district' => 'Colombo',
        'city' => 'Colombo'
    ]);

    $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
        ->deleteJson("/api/v1/registrations/{$registration->id}/force");

    $response->assertStatus(200);
    $response->assertJsonPath('status', 'success');
    
    $this->assertDatabaseMissing('registrations', [
        'id' => $registration->id
    ]);
});
