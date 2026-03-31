<?php

use App\Http\Controllers\V1\Public\CMSController;
use App\Http\Controllers\V1\Public\PublicCourseController;
use App\Http\Controllers\V1\Public\PublicCategoryController;
use App\Http\Controllers\V1\Public\PublicCertificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/public')->group(function () {

    /* Category Public Routes */
    Route::get('categories', [PublicCategoryController::class, 'index']);
    Route::get('categories/{id_or_slug}', [PublicCategoryController::class, 'show']);

    /* Course Public Routes */
    Route::get('courses', [PublicCourseController::class, 'index']);
    Route::get('courses/registration-list', [PublicCourseController::class, 'getRegistrationList']);
    Route::get('courses/{id_or_slug}', [PublicCourseController::class, 'show']);

    /* Certification Public Routes */
    Route::post('certifications/verify', [PublicCertificationController::class, 'verify']);

    /* CMS Public Routes */
    Route::get('cms/{page}', [CMSController::class, 'getPageContent']);
});
