<?php

use App\Http\Controllers\V1\ActivityLogController;
use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\CategoryController;
use App\Http\Controllers\V1\PermissionController;
use App\Http\Controllers\V1\RoleController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\CourseController;
use Illuminate\Support\Facades\Route;


/* public routes */

Route::prefix('v1')->group(function () {
    /* login */
    Route::post('login', [AuthController::class, 'login']);
});

/* protected routes */
Route::middleware(['auth:api'])->prefix('v1')->group(function () {

    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::apiResource('permissions', PermissionController::class);

    Route::get('roles/list/', [RoleController::class, 'getAvailableRoles']);
    Route::apiResource('roles', RoleController::class);

    Route::apiResource('users', UserController::class);
    Route::prefix('users')->group(function () {
        Route::patch('{id}/activate', [UserController::class, 'activate']);
        Route::patch('{id}/deactivate', [UserController::class, 'deactivate']);
        Route::patch('{id}/profile-image', [UserController::class, 'updateProfileImage']);
        Route::delete('{id}/profile-image', [UserController::class, 'removeProfileImage']);
    });

    Route::apiResource('categories', CategoryController::class);
    Route::prefix('categories')->group(function () {
        Route::get('active/list', [CategoryController::class, 'getActiveList']);
    });

    Route::get('tags', [CourseController::class, 'getTags']);
    Route::apiResource('courses', CourseController::class);
    Route::prefix('courses')->group(function () {
        Route::get('active/list', [CourseController::class, 'getActiveList']);
        Route::patch('{id}/restore', [CourseController::class, 'restore']);
        Route::delete('{id}/force', [CourseController::class, 'forceDelete']);
        Route::patch('{id}/toggle-active', [CourseController::class, 'toggleActive']);
        Route::patch('{id}/toggle-registration', [CourseController::class, 'toggleShowInRegistration']);
        Route::patch('{id}/toggle-is-new', [CourseController::class, 'toggleIsNew']);
    });

    Route::prefix('activity-logs')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index']);
        Route::get('{id}', [ActivityLogController::class, 'show']);
    });
});
