<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\Staff;
use App\Traits\ActivityLogTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaffController extends Controller implements HasMiddleware
{
    use ActivityLogTrait, FileUploadTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:Staff Index', ['only' => ['index', 'show']]),
            new Middleware('permission:Staff Create', ['only' => ['store']]),
            new Middleware('permission:Staff Update', ['only' => ['update', 'toggleActive']]),
            new Middleware('permission:Staff Delete', ['only' => ['destroy']]),
        ];
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = Staff::query();

            // Search
            if ($request->has('search') && $request->search != '') {
                $query->search($request->search);
            }

            // Active Filter
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $staff = $query->ordered()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Staff members retrieved successfully',
                'data' => $staff
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve staff members',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function store(CreateStaffRequest $request)
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
                    'staff/',
                    $data['slug']
                );

                if ($imagePath) {
                    $data['profile_image'] = $imagePath;
                }
            }

            $staff = Staff::create($data);

            DB::commit();

            $this->logActivity('CREATE', 'Staff', "Created staff member: {$staff->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Staff member created successfully',
                'data' => $staff
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create staff member',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $staff = Staff::find($id);

            if (!$staff) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Staff member not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Staff member retrieved successfully',
                'data' => $staff
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve staff member',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(UpdateStaffRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $staff = Staff::find($id);

            if (!$staff) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Staff member not found'
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
                    $staff->profile_image,
                    'staff/',
                    $data['slug'] ?? $staff->slug
                );

                if ($imagePath) {
                    $data['profile_image'] = $imagePath;
                }
            }

            $staff->update($data);

            DB::commit();

            $this->logActivity('UPDATE', 'Staff', "Updated staff member: {$staff->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Staff member updated successfully',
                'data' => $staff
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update staff member',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $staff = Staff::find($id);

            if (!$staff) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Staff member not found'
                ], 404);
            }

            // Delete image from storage
            if ($staff->profile_image) {
                $this->deleteFile($staff->profile_image);
            }

            $name = $staff->name;
            $staff->delete();

            $this->logActivity('DELETE', 'Staff', "Permanently deleted staff member: {$name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Staff member deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete staff member',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function toggleActive(string $id)
    {
        try {
            $staff = Staff::find($id);

            if (!$staff) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Staff member not found'
                ], 404);
            }

            $staff->is_active = !$staff->is_active;
            $staff->save();

            $status = $staff->is_active ? 'activated' : 'deactivated';
            $this->logActivity('TOGGLE_ACTIVE', 'Staff', "Changed staff status to {$status}: {$staff->name}");

            return response()->json([
                'status' => 'success',
                'message' => "Staff member {$status} successfully",
                'data' => $staff
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
            $staff = Staff::active()->ordered()->get(['id', 'name', 'slug', 'designation', 'employee_number', 'profile_image']);

            return response()->json([
                'status' => 'success',
                'message' => 'Active staff members retrieved successfully',
                'data' => $staff
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve active staff members',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
