<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed essential permissions
    Permission::firstOrCreate([
        'name' => 'Dashboard View',
        'group_name' => 'Dashboard Permissions',
        'guard_name' => 'api',
    ]);

    // Create a Super Admin role and assign permission
    $role = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'Super Admin']);
    $role->syncPermissions(['Dashboard View']);
});

it('blocks unauthenticated requests to the dashboard', function () {
    $response = $this->getJson('/api/v1/dashboard');

    $response->assertStatus(401);
});

it('blocks users without Dashboard View permission', function () {
    // Create an active user with no roles or permissions
    $user = User::factory()->create([
        'is_active' => true,
        'can_login' => true,
    ]);

    $token = JWTAuth::fromUser($user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/v1/dashboard');

    $response->assertStatus(403);
});

it('allows super admin or permitted user to access dashboard stats', function () {
    $user = User::factory()->create([
        'is_active' => true,
        'can_login' => true,
    ]);
    
    // Assign Super Admin role to user
    $user->assignRole('Super Admin');

    $token = JWTAuth::fromUser($user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/v1/dashboard');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'summary' => [
                    'pathways' => ['total', 'active', 'inactive', 'growth_rate'],
                    'registrations' => [
                        'total',
                        'pending',
                        'approved',
                        'rejected',
                        'growth_rate',
                        'by_program_type' => ['course', 'program'],
                    ],
                    'new_students' => ['count', 'growth_rate'],
                    'courses' => ['total', 'active', 'inactive', 'new', 'growth_rate'],
                    'batches' => ['total', 'active', 'inactive'],
                    'lecturers' => ['total', 'active', 'inactive'],
                    'staff' => ['total', 'active', 'inactive'],
                ],
                'analytics',
                'pathway_breakdown',
                'status_breakdown',
                'recent_registrations',
                'pending_contacts_count',
            ],
        ]);
});
