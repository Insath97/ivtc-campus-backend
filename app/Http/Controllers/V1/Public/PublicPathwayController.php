<?php

namespace App\Http\Controllers\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Pathway;
use Illuminate\Http\Request;

class PublicPathwayController extends Controller
{
    /**
     * Display a listing of active pathways.
     */
    public function index()
    {
        try {
            $pathways = Pathway::active()->ordered()->get(['id', 'name', 'slug', 'description']);

            return response()->json([
                'status' => 'success',
                'message' => 'Public pathways retrieved successfully',
                'data' => $pathways
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve public pathways',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
