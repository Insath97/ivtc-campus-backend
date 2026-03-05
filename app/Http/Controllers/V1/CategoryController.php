<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\ActivityLog;
use App\Traits\ActivityLogTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ActivityLogTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = Category::query();

            if ($request->has('search')) {
                $search = $request->search;
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('slug', 'LIKE', "%{$search}%");
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
                'error' => $th->getMessage()
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
                'error' => $th->getMessage()
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
                'error' => $th->getMessage()
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
                'error' => $th->getMessage()
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
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
