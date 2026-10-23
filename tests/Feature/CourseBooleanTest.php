<?php

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function createCourseAdmin(): User
{
    $user = User::factory()->create();

    foreach (['Course Index', 'Course Create', 'Course Update', 'Course Toggle Active'] as $permission) {
        Permission::firstOrCreate(
            ['name' => $permission, 'guard_name' => 'api'],
            ['group_name' => 'Course Permissions']
        );
        $user->givePermissionTo($permission);
    }

    return $user;
}

function authRequest(User $user)
{
    $token = auth('api')->login($user);

    return test()->withHeader('Authorization', 'Bearer '.$token);
}

function createTestCourse(array $attributes = []): Course
{
    $category = Category::create([
        'name' => 'Category '.uniqid(),
        'slug' => 'category-'.uniqid(),
    ]);

    return Course::create(array_merge([
        'category_id' => $category->id,
        'name' => 'Course '.uniqid(),
        'slug' => 'course-'.uniqid(),
        'code' => 'CRS'.uniqid(),
        'duration' => 3,
        'duration_unit' => 'month',
        'level' => 'Beginner',
        'medium' => 'English',
        'short_description' => 'Short description',
        'full_description' => 'Full description',
    ], $attributes));
}

test('course booleans are mass assignable on the model', function () {
    $course = createTestCourse([
        'is_active' => false,
        'show_in_registration' => false,
        'is_new' => false,
        'has_certificate' => false,
    ]);

    $course->refresh();

    expect($course->is_active)->toBeFalse()
        ->and($course->show_in_registration)->toBeFalse()
        ->and($course->is_new)->toBeFalse()
        ->and($course->has_certificate)->toBeFalse();
});

test('course model activate and deactivate helpers persist', function () {
    $course = createTestCourse(['is_active' => false]);

    $course->activate();
    expect($course->fresh()->is_active)->toBeTrue();

    $course->deactivate();
    expect($course->fresh()->is_active)->toBeFalse();
});

test('course update endpoint persists boolean flags', function () {
    $user = createCourseAdmin();
    $course = createTestCourse();

    authRequest($user)
        ->patchJson("/api/v1/courses/{$course->id}", [
            'is_active' => false,
            'show_in_registration' => false,
            'is_new' => false,
            'has_certificate' => false,
        ])
        ->assertStatus(200)
        ->assertJsonPath('data.is_active', false)
        ->assertJsonPath('data.show_in_registration', false)
        ->assertJsonPath('data.is_new', false)
        ->assertJsonPath('data.has_certificate', false);

    $course->refresh();

    expect($course->is_active)->toBeFalse()
        ->and($course->show_in_registration)->toBeFalse()
        ->and($course->is_new)->toBeFalse()
        ->and($course->has_certificate)->toBeFalse();
});

test('course update endpoint persists boolean flags sent as strings', function () {
    $user = createCourseAdmin();
    $course = createTestCourse();

    authRequest($user)
        ->patchJson("/api/v1/courses/{$course->id}", [
            'is_active' => '0',
            'show_in_registration' => '0',
            'is_new' => '0',
            'has_certificate' => '0',
        ])
        ->assertStatus(200);

    $course->refresh();

    expect($course->is_active)->toBeFalse()
        ->and($course->show_in_registration)->toBeFalse()
        ->and($course->is_new)->toBeFalse()
        ->and($course->has_certificate)->toBeFalse();
});

test('course store endpoint persists boolean flags', function () {
    $user = createCourseAdmin();
    $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid()]);

    authRequest($user)
        ->postJson('/api/v1/courses', [
            'category_id' => $category->id,
            'name' => 'New Course',
            'code' => 'NC001',
            'duration' => 6,
            'duration_unit' => 'month',
            'level' => 'Beginner',
            'medium' => 'English',
            'short_description' => 'short',
            'full_description' => 'long',
            'is_active' => false,
            'show_in_registration' => false,
            'is_new' => false,
            'has_certificate' => false,
        ])
        ->assertStatus(201);

    $course = Course::where('code', 'NC001')->firstOrFail();

    expect($course->is_active)->toBeFalse()
        ->and($course->show_in_registration)->toBeFalse()
        ->and($course->is_new)->toBeFalse()
        ->and($course->has_certificate)->toBeFalse();
});

test('admin course show exposes boolean flags', function () {
    $user = createCourseAdmin();
    $course = createTestCourse();

    authRequest($user)
        ->getJson("/api/v1/courses/{$course->id}")
        ->assertStatus(200)
        ->assertJsonPath('data.is_active', true)
        ->assertJsonPath('data.show_in_registration', true)
        ->assertJsonPath('data.is_new', true)
        ->assertJsonPath('data.has_certificate', true);
});

test('admin index filters boolean flags with false value', function () {
    $user = createCourseAdmin();
    $active = createTestCourse();
    $inactive = createTestCourse(['is_active' => false]);

    $response = authRequest($user)
        ->getJson('/api/v1/courses?is_active=false')
        ->assertStatus(200);

    $ids = collect($response->json('data.data'))->pluck('id');

    expect($ids->all())->toContain($inactive->id)
        ->not->toContain($active->id);
});

test('toggle active endpoint still works and exposes the flag', function () {
    $user = createCourseAdmin();
    $course = createTestCourse(['is_active' => true]);

    authRequest($user)
        ->patchJson("/api/v1/courses/{$course->id}/toggle-active")
        ->assertStatus(200)
        ->assertJsonPath('data.is_active', false);

    expect($course->fresh()->is_active)->toBeFalse();
});

test('public course detail hides admin only flags but keeps is_new', function () {
    $course = createTestCourse();

    $response = $this->getJson("/api/v1/public/courses/{$course->slug}")
        ->assertStatus(200);

    $data = $response->json('data');

    expect($data)->not->toHaveKey('is_active')
        ->not->toHaveKey('show_in_registration')
        ->toHaveKey('is_new');
});

test('public course index exposes is_new flag', function () {
    $course = createTestCourse();

    $response = $this->getJson('/api/v1/public/courses')
        ->assertStatus(200);

    $items = $response->json('data.data');

    expect(collect($items)->pluck('id')->all())->toContain($course->id);
    expect($items[0])->toHaveKey('is_new');
});
