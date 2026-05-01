<?php

namespace App\Http\Controllers\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Pastpaper;
use Illuminate\Http\Request;

class PublicPastPaperController extends Controller
{
    /**
     * Display a listing of public active past papers.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = Pastpaper::with(['batch:id,name'])->active();

            // Search
            if ($request->has('search') && $request->search != '') {
                $query->search($request->search);
            }

            // Batch Filter
            if ($request->has('batch_id') && $request->batch_id != '') {
                $query->where('batch_id', $request->batch_id);
            }

            // Has Scheme Filter
            if ($request->has('has_scheme')) {
                $query->where('has_scheme', $request->boolean('has_scheme'));
            }

            $papers = $query->ordered()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Public past papers retrieved successfully',
                'data' => $papers
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve public past papers',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
