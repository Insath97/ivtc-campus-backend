<?php

namespace App\Http\Controllers\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class PublicCourseController extends Controller
{
    /**
     * Display a listing of public courses.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 12);
            $query = Course::active()->ordered()
                ->with(['category:id,name', 'tags:id,name', 'images:id,course_id,image_path', 'videos']);

            // Search
            if ($request->has('search') && $request->search != '') {
                $query->search($request->search);
            }

            // Category Filter
            if ($request->has('category_id') && $request->category_id != '') {
                $query->where('category_id', $request->category_id);
            }

            // Level Filter
            if ($request->has('level') && $request->level != '') {
                $query->where('level', $request->level);
            }

            // Medium Filter
            if ($request->has('medium') && $request->medium != '') {
                $query->where('medium', $request->medium);
            }

            // New Arrival Filter
            if ($request->has('is_new')) {
                $query->where('is_new', $request->is_new);
            }

            $courses = $query->paginate($perPage, [
                'id', 'category_id', 'name', 'slug', 'code', 'primary_image',
                'duration', 'duration_unit', 'level', 'medium', 'short_description', 'is_new', 'has_certificate'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Public courses retrieved successfully',
                'data' => $courses
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve public courses',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified public course.
     */
    public function show(string $id_or_slug)
    {
        try {
            $query = Course::active()->with([
                'category:id,name,slug',
                'tags:id,name,slug',
                'images:id,course_id,image_path',
                'videos'
            ]);

            if (is_numeric($id_or_slug)) {
                $course = $query->find($id_or_slug);
            } else {
                $course = $query->where('slug', $id_or_slug)->first();
            }

            if (!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course not found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Public course detail retrieved successfully',
                'data' => $course
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve public course detail',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get a list of active courses for registration select box.
     */
    public function getRegistrationList()
    {
        try {
            $courses = Course::active()->forRegistration()->ordered()->get(['id', 'name', 'code']);

            return response()->json([
                'status' => 'success',
                'message' => 'Active registration courses retrieved successfully',
                'data' => $courses
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve registration course list',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
