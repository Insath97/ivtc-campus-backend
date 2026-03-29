<?php

use Illuminate\Support\Facades\Route;

// Health Check endpoint
Route::get('/health-check', function () {
    return response()->json(['message' => 'IVTC Campus API is working!']);
});

/* version 1 routes */
require __DIR__ . '/v1.php';

/* public routes */
require __DIR__ . '/public.php';
