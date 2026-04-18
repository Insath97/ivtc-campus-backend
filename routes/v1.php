<?php

use App\Http\Controllers\V1\ActivityLogController;
use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\CategoryController;
use App\Http\Controllers\V1\PermissionController;
use App\Http\Controllers\V1\RoleController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\CourseController;
use App\Http\Controllers\V1\CertificationController;
use App\Http\Controllers\V1\CmsContentController;
use App\Http\Controllers\V1\PathwayController;
use App\Http\Controllers\V1\RegistrationProgramController;
use App\Http\Controllers\V1\RegistrationController;
use App\Http\Controllers\V1\ContactController;
use App\Http\Controllers\V1\SettingController;
use App\Http\Controllers\V1\BatchController;
use App\Http\Controllers\V1\LearningMaterialController;
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

    Route::get('permissions/list/', [PermissionController::class, 'getAvailablePermissions']);
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

    Route::apiResource('certifications', CertificationController::class);
    Route::prefix('certifications')->group(function () {
        Route::post('import', [CertificationController::class, 'bulkImport']);
        Route::patch('{id}/restore', [CertificationController::class, 'restore']);
        Route::delete('{id}/force', [CertificationController::class, 'forceDelete']);
        Route::patch('{id}/toggle-active', [CertificationController::class, 'toggleActive']);
    });

    Route::prefix('cms')->group(function () {
        Route::get('/', [CmsContentController::class, 'index']);
        Route::post('update', [CmsContentController::class, 'update']);
    });

    Route::apiResource('pathways', PathwayController::class);
    Route::prefix('pathways')->group(function () {
        Route::get('active/list', [PathwayController::class, 'getActiveList']);
        Route::patch('{id}/toggle-active', [PathwayController::class, 'toggleActive']);
    });

    Route::apiResource('registration-programs', RegistrationProgramController::class);
    Route::prefix('registration-programs')->group(function () {
        Route::get('active/list', [RegistrationProgramController::class, 'getActiveList']);
        Route::get('pathway/{pathway_id}', [RegistrationProgramController::class, 'getByPathway']);
        Route::patch('{id}/toggle-active', [RegistrationProgramController::class, 'toggleActive']);
    });

    Route::apiResource('registrations', RegistrationController::class)->except(['store', 'update']);
    Route::prefix('registrations')->group(function () {
        Route::patch('{id}/approve', [RegistrationController::class, 'approve']);
        Route::patch('{id}/reject', [RegistrationController::class, 'reject']);
    });

    Route::apiResource('contacts', ContactController::class)->except(['store', 'update']);
    Route::prefix('contacts')->group(function () {
        Route::post('{id}/reply', [ContactController::class, 'reply']);
    });

    Route::prefix('setting')->group(function () {
        Route::get('/', [SettingController::class, 'index']);
        Route::post('/', [SettingController::class, 'update']);
    });

    Route::apiResource('batches', BatchController::class);
    Route::prefix('batches')->group(function () {
        Route::get('active/list', [BatchController::class, 'getActiveList']);
        Route::patch('{id}/toggle-active', [BatchController::class, 'toggleActive']);
    });

    Route::apiResource('learning-materials', LearningMaterialController::class);
    Route::prefix('learning-materials')->group(function () {
        Route::get('active/list', [LearningMaterialController::class, 'getActiveList']);
        Route::patch('{id}/restore', [LearningMaterialController::class, 'restore']);
        // Match the user's requested pattern for soft deletes (Course pattern)
        Route::delete('{id}/force', [LearningMaterialController::class, 'forceDelete']);
        Route::patch('{id}/toggle-active', [LearningMaterialController::class, 'toggleActive']);
    });
});
