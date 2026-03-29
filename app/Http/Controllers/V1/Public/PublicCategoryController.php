<?php

namespace App\Http\Controllers\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class PublicCategoryController extends Controller
{
    /**
     * Display a listing of public categories.
     */
    public function index()
    {
        try {
            $categories = Category::active()->ordered()->get(['id', 'name', 'slug']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Public categories retrieved successfully',
                'data' => $categories
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve public categories',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified public category detail.
     */
    public function show(string $id_or_slug)
    {
        try {
            $query = Category::active();
            
            if (is_numeric($id_or_slug)) {
                $category = $query->find($id_or_slug);
            } else {
                $category = $query->where('slug', $id_or_slug)->first();
            }

            if (!$category) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found',
                    'data' => []
                ], 404);
            }

            // Load active courses for this category
            $category->load(['courses' => function ($q) {
                $q->active()->ordered()->select([
                    'id', 'category_id', 'name', 'slug', 'code', 'primary_image', 
                    'duration', 'duration_unit', 'level', 'medium', 'short_description', 'has_certificate'
                ]);
            }]);

            return response()->json([
                'status' => 'success',
                'message' => 'Public category detail retrieved successfully',
                'data' => $category
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve public category detail',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
