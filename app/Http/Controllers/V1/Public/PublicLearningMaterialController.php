<?php

namespace App\Http\Controllers\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use Illuminate\Http\Request;

class PublicLearningMaterialController extends Controller
{
    /**
     * Display a listing of public active learning materials.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = LearningMaterial::with(['batch:id,name'])->active();

            // Search
            if ($request->has('search') && $request->search != '') {
                $query->search($request->search);
            }

            // Batch Filter
            if ($request->has('batch_id') && $request->batch_id != '') {
                $query->where('batch_id', $request->batch_id);
            }

            // Type Filter
            if ($request->has('material_type') && $request->material_type != '') {
                $query->where('material_type', $request->material_type);
            }

            // For public access, we might not want to expose creator data or soft deleted items.
            $materials = $query->ordered()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Public learning materials retrieved successfully',
                'data' => $materials
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve public learning materials',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
