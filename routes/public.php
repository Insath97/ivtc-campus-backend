<?php

use App\Http\Controllers\V1\Public\CMSController;
use App\Http\Controllers\V1\Public\PublicCourseController;
use App\Http\Controllers\V1\Public\PublicCategoryController;
use App\Http\Controllers\V1\Public\PublicCertificationController;
use App\Http\Controllers\V1\Public\PublicPathwayController;
use App\Http\Controllers\V1\Public\PublicRegistrationController;
use App\Http\Controllers\V1\Public\PublicContactController;
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

    /* Registration Public Routes */
    Route::get('pathways', [PublicPathwayController::class, 'index']);
    Route::get('registration/programs/{pathway_id}', [PublicRegistrationController::class, 'getProgramsByPathway']);
    Route::post('registration/submit', [PublicRegistrationController::class, 'store']);

    /* Contact Public Routes */
    Route::post('contact', [PublicContactController::class, 'store']);
});
