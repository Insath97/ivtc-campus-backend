<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\CourseImage;
use App\Models\Tag;
use App\Traits\ActivityLogTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseController extends Controller implements HasMiddleware
{
    use ActivityLogTrait, FileUploadTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:Course Index', ['only' => ['index', 'show']]),
            new Middleware('permission:Course Create', ['only' => ['store']]),
            new Middleware('permission:Course Update', ['only' => ['update']]),
            new Middleware('permission:Course Soft Delete', ['only' => ['destroy']]),
            new Middleware('permission:Course Force Delete', ['only' => ['forceDelete']]),
            new Middleware('permission:Course Restore', ['only' => ['restore']]),
            new Middleware('permission:Course Toggle Active', ['only' => ['toggleActive']]),
            new Middleware('permission:Course Toggle Registration', ['only' => ['toggleShowInRegistration']]),
            new Middleware('permission:Course Toggle New', ['only' => ['toggleIsNew']]),
        ];
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = Course::with(['category', 'tags', 'images', 'videos']);

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

            // Active Filter
            if ($request->has('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // New Arrival Filter
            if ($request->has('is_new')) {
                $query->where('is_new', $request->is_new);
            }

            // Registration Filter
            if ($request->has('show_in_registration')) {
                $query->where('show_in_registration', $request->show_in_registration);
            }

            // Soft Deleted Filter
            if ($request->has('trashed') && $request->trashed == 'true') {
                $query->onlyTrashed();
            }

            $courses = $query->orderBy('name', 'asc')->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Courses retrieved successfully',
                'data' => $courses
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve courses',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function store(CreateCourseRequest $request)
    {
        DB::beginTransaction();
        try {
            $currentUser = auth('api')->user();
            $data = $request->validated();

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $data['created_by'] = $currentUser->id;

            // Handle primary image upload
            $primaryImagePath = $this->handleFileUpload(
                $request,
                'primary_image',
                null,
                'course/primary_image/',
                $data['code']
            );

            if ($primaryImagePath) {
                $data['primary_image'] = $primaryImagePath;
            }

            $course = Course::create($data);

            /* handle multi images */
            $courseImages = $this->handleMultipleFileUpload(
                $request,
                'images',
                [],
                'courses/' . $course->id . '/images',
                $data['code'] . '_images'
            );

            foreach ($courseImages as $imagePath) {
                CourseImage::create(
                    [
                        'course_id' => $course->id,
                        'image_path' => $imagePath
                    ]
                );
            }

            // Add Video Files
            $courseVideoFiles = $this->handleMultipleFileUpload(
                $request,
                'video_files',
                [],
                'courses/' . $course->id . '/videos',
                $data['code'] . '_v'
            );

            foreach ($courseVideoFiles as $index => $videoPath) {
                $course->videos()->create([
                    'video_url' => $videoPath,
                    'title' => $request->video_file_titles[$index] ?? null,
                ]);
            }

            // Add Video URLs
            if (isset($data['videos'])) {
                foreach ($data['videos'] as $video) {
                    if (!empty($video['url'])) {
                        $course->videos()->create([
                            'video_url' => $video['url'],
                            'title' => $video['title'] ?? null,
                        ]);
                    }
                }
            }

            /* handle tags */
            if (!empty($data['tags'])) {
                $tags = explode(',', $data['tags']);
                $tagIds = [];

                foreach ($tags as $tag) {
                    $tag = trim($tag);

                    if ($tag === '') {
                        continue;
                    }

                    $tagModel = Tag::firstOrCreate(
                        ['name' => $tag],
                        ['slug' => Str::slug($tag)]
                    );

                    $tagIds[] = $tagModel->id;
                }

                $course->tags()->sync($tagIds);
            }

            DB::commit();

            $this->logActivity('CREATE', 'Course', "Created course: {$course->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Course created successfully',
                'data' => $course->load(['category', 'tags', 'images', 'videos'])
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create course',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $course = Course::with(['category:id,name', 'tags:id,name', 'images:id,course_id,image_path', 'videos'])->find($id);

            if (!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Course retrieved successfully',
                'data' => $course
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve course',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(UpdateCourseRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $course = Course::with(['images', 'videos', 'tags'])->find($id);

            if (!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course not found',
                    'data' => []
                ], 404);
            }

            $data = $request->validated();

            if (isset($data['name']) && empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            // Handle primary image update
            if ($request->hasFile('primary_image')) {
                $primaryImagePath = $this->handleFileUpload(
                    $request,
                    'primary_image',
                    $course->primary_image,
                    'course/primary_image/',
                    $data['code'] ?? $course->code
                );

                if ($primaryImagePath) {
                    $data['primary_image'] = $primaryImagePath;
                }
            }

            $course->update($data);

            /* handle multi images update (Replacement Strategy) */
            if ($request->hasFile('images')) {
                // Delete old images from storage
                $oldImagePaths = $course->images->pluck('image_path')->toArray();
                $this->deleteMultipleFiles($oldImagePaths);

                // Delete from DB
                $course->images()->delete();

                $uploadedImages = $this->handleMultipleFileUpload(
                    $request,
                    'images',
                    [],
                    'courses/' . $course->id . '/images',
                    ($data['code'] ?? $course->code) . '_images'
                );

                foreach ($uploadedImages as $imagePath) {
                    CourseImage::create([
                        'course_id' => $course->id,
                        'image_path' => $imagePath
                    ]);
                }
            }

            /* handle videos (files and URLs) update (Replacement Strategy) */
            if ($request->hasFile('video_files') || isset($data['videos'])) {
                // Delete old video files from storage (only those that are local paths)
                foreach ($course->videos as $video) {
                    if (!filter_var($video->video_url, FILTER_VALIDATE_URL)) {
                        $this->deleteFile($video->video_url);
                    }
                }

                // Delete from DB
                $course->videos()->delete();

                // Add Video Files
                if ($request->hasFile('video_files')) {
                    $courseVideoFiles = $this->handleMultipleFileUpload(
                        $request,
                        'video_files',
                        [],
                        'courses/' . $course->id . '/videos',
                        ($data['code'] ?? $course->code) . '_v'
                    );

                    foreach ($courseVideoFiles as $index => $videoPath) {
                        $course->videos()->create([
                            'video_url' => $videoPath,
                            'title' => $request->video_file_titles[$index] ?? null,
                        ]);
                    }
                }

                // Add Video URLs
                if (isset($data['videos'])) {
                    foreach ($data['videos'] as $video) {
                        if (!empty($video['url'])) {
                            $course->videos()->create([
                                'video_url' => $video['url'],
                                'title' => $video['title'] ?? null,
                            ]);
                        }
                    }
                }
            }

            /* handle tags */
            if (isset($data['tags'])) {
                $tags = explode(',', $data['tags']);
                $tagIds = [];

                foreach ($tags as $tag) {
                    $tag = trim($tag);
                    if ($tag === '') continue;

                    $tagModel = Tag::firstOrCreate(
                        ['name' => $tag],
                        ['slug' => Str::slug($tag)]
                    );
                    $tagIds[] = $tagModel->id;
                }
                $course->tags()->sync($tagIds);
            }

            DB::commit();

            $this->logActivity('UPDATE', 'Course', "Updated course: {$course->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Course updated successfully',
                'data' => $course->load(['category:id,name', 'tags:id,name', 'images:id,course_id,image_path', 'videos'])
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update course',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $course = Course::find($id);

            if (!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course not found',
                    'data' => []
                ], 404);
            }

            $name = $course->name;
            $course->delete();

            $this->logActivity('DELETE', 'Course', "Soft deleted course: {$name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Course soft deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete course',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function restore(string $id)
    {
        try {
            $course = Course::onlyTrashed()->find($id);

            if (!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Trashed course not found',
                    'data' => []
                ], 404);
            }

            if (!$course->trashed()) {
                return response()->json([
                    'status' => 'info',
                    'message' => 'Course is not deleted',
                    'data' => $course
                ], 200);
            }

            $course->restore();

            $this->logActivity('RESTORE', 'Course', "Restored course: {$course->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Course restored successfully',
                'data' => $course->load(['category', 'tags', 'images', 'videos'])
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to restore course',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function forceDelete(string $id)
    {
        try {
            $course = Course::withTrashed()->with(['images', 'videos'])->find($id);

            if (!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course not found',
                    'data' => []
                ], 404);
            }

            // 1. Delete Primary Image from storage
            if (!empty($course->primary_image)) {
                $this->deleteFile($course->primary_image);
            }

            // 2. Delete Course Images from storage
            $imagePaths = $course->images->pluck('image_path')->toArray();
            $this->deleteMultipleFiles($imagePaths);

            // 3. Delete Course Videos from storage (local paths only)
            foreach ($course->videos as $video) {
                if (!filter_var($video->video_url, FILTER_VALIDATE_URL)) {
                    $this->deleteFile($video->video_url);
                }
            }

            $name = $course->name;

            // Delete from DB (onDelete cascade will handle child records)
            $course->forceDelete();

            $this->logActivity('FORCE_DELETE', 'Course', "Permanently deleted course: {$name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Course and all associated files permanently deleted'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to force delete course',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function toggleActive(string $id)
    {
        try {
            $course = Course::find($id);

            if (!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course not found'
                ], 404);
            }

            $course->is_active = !$course->is_active;
            $course->save();

            $status = $course->is_active ? 'activated' : 'deactivated';
            $this->logActivity('TOGGLE_ACTIVE', 'Course', "Changed course status to {$status}: {$course->name}");

            return response()->json([
                'status' => 'success',
                'message' => "Course {$status} successfully",
                'data' => $course->load(['category:id,name', 'tags:id,name', 'images:id,course_id,image_path', 'videos'])
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to toggle active status',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function toggleShowInRegistration(string $id)
    {
        try {
            $course = Course::find($id);

            if (!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course not found'
                ], 404);
            }

            $course->show_in_registration = !$course->show_in_registration;
            $course->save();

            $status = $course->show_in_registration ? 'enabled' : 'disabled';
            $this->logActivity('TOGGLE_REGISTRATION', 'Course', "registration display {$status} for: {$course->name}");

            return response()->json([
                'status' => 'success',
                'message' => "Registration display {$status} successfully",
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to toggle registration status',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function toggleIsNew(string $id)
    {
        try {
            $course = Course::find($id);

            if (!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course not found'
                ], 404);
            }

            $course->is_new = !$course->is_new;
            $course->save();

            $status = $course->is_new ? 'marked as new' : 'unmarked as new';
            $this->logActivity('TOGGLE_IS_NEW', 'Course', "Course {$status}: {$course->name}");

            return response()->json([
                'status' => 'success',
                'message' => "Course {$status} successfully",
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to toggle new status',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function getActiveList()
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
                'message' => 'Failed to retrieve active courses',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function publicCourses(Request $request)
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
                'duration', 'duration_unit', 'level', 'medium', 'short_description', 'is_new'
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

    public function publicCourseByDetail(string $id_or_slug)
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
}
