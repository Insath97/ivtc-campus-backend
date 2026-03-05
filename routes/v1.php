<?php

use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\LevelController;
use App\Http\Controllers\V1\PermissionController;
use App\Http\Controllers\V1\RoleController;
use App\Http\Controllers\V1\UserController;
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

    
    /* levels routes */
    Route::apiResource('levels', LevelController::class);
});
