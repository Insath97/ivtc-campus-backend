<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Traits\ActivityLogTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;

class CategoryController extends Controller implements HasMiddleware
{
    use ActivityLogTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:Category Index', ['only' => ['index', 'show']]),
            new Middleware('permission:Category Create', ['only' => ['store']]),
            new Middleware('permission:Category Update', ['only' => ['update']]),
            new Middleware('permission:Category Delete', ['only' => ['destroy']]),
        ];
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = Category::query();

            // Search
            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $query->search($search);
            }

            $categories = $query->orderBy('name', 'asc')->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Categories retrieved successfully',
                'data' => $categories
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve categories',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function store(CreateCategoryRequest $request)
    {
        try {
            $data = $request->validated();

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $category = Category::create($data);

            $this->logActivity('CREATE', 'Category', "Created category: {$category->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create category',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Category retrieved successfully',
                'data' => $category
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve category',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(UpdateCategoryRequest $request, string $id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found'
                ], 404);
            }

            $data = $request->validated();

            if (isset($data['name']) && empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $category->update($data);

            $this->logActivity('UPDATE', 'Category', "Updated category: {$category->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Category updated successfully',
                'data' => $category
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update category',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found'
                ], 404);
            }

            $categoryName = $category->name;
            $category->delete();

            $this->logActivity('DELETE', 'Category', "Deleted category: {$categoryName}");

            return response()->json([
                'status' => 'success',
                'message' => 'Category deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete category',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function getActiveList()
    {
        try {
            $categories = Category::active()->ordered()->get(['id', 'name', 'slug']);

            if ($categories->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No active categories found',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Active categories retrieved successfully',
                'data' => $categories
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve active categories',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function publicCategories()
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

    public function publicCategoryByDetail(string $id_or_slug)
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
                $q->active()->ordered()->select(['id', 'category_id', 'name', 'slug', 'code', 'primary_image', 'duration', 'duration_unit', 'level', 'medium', 'short_description']);
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
