<?php

namespace App\Http\Controllers\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Batch;

class PublicBatchController extends Controller
{
    /**
     * Display a listing of public active batches.
     */
    public function index()
    {
        try {
            $batches = Batch::active()->ordered()->get(['id', 'name', 'slug', 'year', 'description']);

            return response()->json([
                'status' => 'success',
                'message' => 'Public batches retrieved successfully',
                'data' => $batches
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve public batches',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
