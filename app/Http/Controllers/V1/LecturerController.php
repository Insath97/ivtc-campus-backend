<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateLecturerRequest;
use App\Http\Requests\UpdateLecturerRequest;
use App\Models\Lecturer;
use App\Traits\ActivityLogTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LecturerController extends Controller implements HasMiddleware
{
    use ActivityLogTrait, FileUploadTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:Lecturer Index', ['only' => ['index', 'show']]),
            new Middleware('permission:Lecturer Create', ['only' => ['store']]),
            new Middleware('permission:Lecturer Update', ['only' => ['update', 'toggleActive']]),
            new Middleware('permission:Lecturer Soft Delete', ['only' => ['destroy']]),
            new Middleware('permission:Lecturer Force Delete', ['only' => ['forceDelete']]),
            new Middleware('permission:Lecturer Restore', ['only' => ['restore']]),
        ];
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = Lecturer::query();

            // Search
            if ($request->has('search') && $request->search != '') {
                $query->search($request->search);
            }

            // Active Filter
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Soft Deleted Filter
            if ($request->has('trashed') && $request->trashed == 'true') {
                $query->onlyTrashed();
            }

            $lecturers = $query->ordered()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Lecturers retrieved successfully',
                'data' => $lecturers
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve lecturers',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function store(CreateLecturerRequest $request)
    {
        DB::beginTransaction();
        try {
            $currentUser = auth('api')->user();
            $data = $request->validated();

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
            }

            $data['created_by'] = $currentUser->id;

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $this->handleFileUpload(
                    $request,
                    'image',
                    null,
                    'lecturers/',
                    $data['slug']
                );

                if ($imagePath) {
                    $data['image'] = $imagePath;
                }
            }

            $lecturer = Lecturer::create($data);

            DB::commit();

            $this->logActivity('CREATE', 'Lecturer', "Created lecturer: {$lecturer->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Lecturer created successfully',
                'data' => $lecturer
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create lecturer',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $lecturer = Lecturer::withTrashed()->find($id);

            if (!$lecturer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lecturer not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Lecturer retrieved successfully',
                'data' => $lecturer
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve lecturer',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(UpdateLecturerRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $lecturer = Lecturer::find($id);

            if (!$lecturer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lecturer not found'
                ], 404);
            }

            $data = $request->validated();

            if (isset($data['name']) && empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
            }

            // Handle image update
            if ($request->hasFile('image')) {
                $imagePath = $this->handleFileUpload(
                    $request,
                    'image',
                    $lecturer->image,
                    'lecturers/',
                    $data['slug'] ?? $lecturer->slug
                );

                if ($imagePath) {
                    $data['image'] = $imagePath;
                }
            }

            $lecturer->update($data);

            DB::commit();

            $this->logActivity('UPDATE', 'Lecturer', "Updated lecturer: {$lecturer->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Lecturer updated successfully',
                'data' => $lecturer
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update lecturer',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $lecturer = Lecturer::find($id);

            if (!$lecturer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lecturer not found'
                ], 404);
            }

            $name = $lecturer->name;
            $lecturer->delete();

            $this->logActivity('DELETE', 'Lecturer', "Soft deleted lecturer: {$name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Lecturer soft deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete lecturer',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function restore(string $id)
    {
        try {
            $lecturer = Lecturer::onlyTrashed()->find($id);

            if (!$lecturer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Trashed lecturer not found'
                ], 404);
            }

            $lecturer->restore();

            $this->logActivity('RESTORE', 'Lecturer', "Restored lecturer: {$lecturer->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Lecturer restored successfully',
                'data' => $lecturer
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to restore lecturer',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function forceDelete(string $id)
    {
        try {
            $lecturer = Lecturer::withTrashed()->find($id);

            if (!$lecturer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lecturer not found'
                ], 404);
            }

            // Delete image from storage
            if ($lecturer->image) {
                $this->deleteFile($lecturer->image);
            }

            $name = $lecturer->name;
            $lecturer->forceDelete();

            $this->logActivity('FORCE_DELETE', 'Lecturer', "Permanently deleted lecturer: {$name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Lecturer permanently deleted'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to force delete lecturer',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function toggleActive(string $id)
    {
        try {
            $lecturer = Lecturer::find($id);

            if (!$lecturer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lecturer not found'
                ], 404);
            }

            $lecturer->is_active = !$lecturer->is_active;
            $lecturer->save();

            $status = $lecturer->is_active ? 'activated' : 'deactivated';
            $this->logActivity('TOGGLE_ACTIVE', 'Lecturer', "Changed lecturer status to {$status}: {$lecturer->name}");

            return response()->json([
                'status' => 'success',
                'message' => "Lecturer {$status} successfully",
                'data' => $lecturer
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to toggle status',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function getActiveList()
    {
        try {
            $lecturers = Lecturer::active()->ordered()->get(['id', 'name', 'slug', 'specialization', 'image']);

            return response()->json([
                'status' => 'success',
                'message' => 'Active lecturers retrieved successfully',
                'data' => $lecturers
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve active lecturers',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
