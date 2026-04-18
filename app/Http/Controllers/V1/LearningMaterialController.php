<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateLearningMaterialRequest;
use App\Http\Requests\UpdateLearningMaterialRequest;
use App\Models\LearningMaterial;
use App\Traits\ActivityLogTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LearningMaterialController extends Controller implements HasMiddleware
{
    use ActivityLogTrait, FileUploadTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:Learning Material Index', ['only' => ['index', 'show']]),
            new Middleware('permission:Learning Material Create', ['only' => ['store']]),
            new Middleware('permission:Learning Material Update', ['only' => ['update']]),
            new Middleware('permission:Learning Material Soft Delete', ['only' => ['destroy']]),
            new Middleware('permission:Learning Material Force Delete', ['only' => ['forceDelete']]),
            new Middleware('permission:Learning Material Restore', ['only' => ['restore']]),
            new Middleware('permission:Learning Material Toggle Active', ['only' => ['toggleActive']]),
        ];
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = LearningMaterial::with(['batch:id,name', 'creator:id,name']);

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

            // Active Filter
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Soft Deleted Filter
            if ($request->has('trashed') && $request->trashed == 'true') {
                $query->onlyTrashed();
            }

            $materials = $query->ordered()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Learning materials retrieved successfully',
                'data' => $materials
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve learning materials',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function store(CreateLearningMaterialRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['created_by'] = auth('api')->id();

            // Handle file upload if type is video or document related
            if (in_array($data['material_type'], ['notes', 'video', 'slides', 'handout', 'podcast', 'ebook']) && $request->hasFile('file')) {
                $filePath = $this->handleFileUpload(
                    $request,
                    'file',
                    null,
                    'learning_materials',
                    Str::slug($data['subject_name']) . '_' . time()
                );

                if ($filePath) {
                    $data['file_path'] = $filePath;
                }
            }

            $material = LearningMaterial::create($data);

            DB::commit();

            $this->logActivity('CREATE', 'LearningMaterial', "Created learning material: {$material->subject_name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Learning material created successfully',
                'data' => $material->load(['batch:id,name', 'creator:id,name'])
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create learning material',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $material = LearningMaterial::with(['batch:id,name', 'creator:id,name'])->find($id);

            if (!$material) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Learning material not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Learning material retrieved successfully',
                'data' => $material
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve learning material',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(UpdateLearningMaterialRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $material = LearningMaterial::find($id);

            if (!$material) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Learning material not found'
                ], 404);
            }

            $data = $request->validated();

            // Handle file upload replacement
            if ($request->hasFile('file')) {
                // Only upload if it's a file-based type
                if (in_array($data['material_type'] ?? $material->material_type, ['notes', 'video', 'slides', 'handout', 'podcast', 'ebook'])) {
                    $filePath = $this->handleFileUpload(
                        $request,
                        'file',
                        $material->file_path,
                        'learning_materials',
                        Str::slug($data['subject_name'] ?? $material->subject_name) . '_' . time()
                    );
    
                    if ($filePath) {
                        $data['file_path'] = $filePath;
                        // If we uploaded a file, we should clear the external_url if it's set
                        $data['external_url'] = null;
                    }
                }
            } elseif (isset($data['material_type']) && in_array($data['material_type'], ['youtube', 'link', 'live_class'])) {
                // If type changed to non-file, delete old file and clear path
                if ($material->file_path) {
                    $this->deleteFile($material->file_path);
                    $data['file_path'] = null;
                }
            }

            $material->update($data);

            DB::commit();

            $this->logActivity('UPDATE', 'LearningMaterial', "Updated learning material: {$material->subject_name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Learning material updated successfully',
                'data' => $material->load(['batch:id,name', 'creator:id,name'])
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update learning material',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $material = LearningMaterial::find($id);

            if (!$material) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Learning material not found'
                ], 404);
            }

            $title = $material->subject_name;
            $material->delete();

            $this->logActivity('DELETE', 'LearningMaterial', "Soft deleted learning material: {$title}");

            return response()->json([
                'status' => 'success',
                'message' => 'Learning material soft deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete learning material',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function restore(string $id)
    {
        try {
            $material = LearningMaterial::onlyTrashed()->find($id);

            if (!$material) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Trashed learning material not found'
                ], 404);
            }

            $material->restore();

            $this->logActivity('RESTORE', 'LearningMaterial', "Restored learning material: {$material->subject_name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Learning material restored successfully',
                'data' => $material->load(['batch:id,name', 'creator:id,name'])
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to restore learning material',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function forceDelete(string $id)
    {
        try {
            $material = LearningMaterial::withTrashed()->find($id);

            if (!$material) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Learning material not found'
                ], 404);
            }

            // Delete associated file if exists
            if ($material->file_path) {
                $this->deleteFile($material->file_path);
            }

            $title = $material->subject_name;
            $material->forceDelete();

            $this->logActivity('FORCE_DELETE', 'LearningMaterial', "Permanently deleted learning material: {$title}");

            return response()->json([
                'status' => 'success',
                'message' => 'Learning material permanently deleted'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to force delete learning material',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function toggleActive(string $id)
    {
        try {
            $material = LearningMaterial::find($id);

            if (!$material) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Learning material not found'
                ], 404);
            }

            $material->is_active = !$material->is_active;
            $material->save();

            $status = $material->is_active ? 'activated' : 'deactivated';
            $this->logActivity('TOGGLE_ACTIVE', 'LearningMaterial', "Changed status to {$status} for: {$material->subject_name}");

            return response()->json([
                'status' => 'success',
                'message' => "Learning material {$status} successfully",
                'data' => $material->load(['batch:id,name', 'creator:id,name'])
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to toggle status',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function getActiveList(Request $request)
    {
        try {
            $query = LearningMaterial::active();

            if ($request->has('batch_id')) {
                $query->where('batch_id', $request->batch_id);
            }

            $materials = $query->ordered()->get(['id', 'subject_name', 'material_type', 'file_path', 'external_url']);

            return response()->json([
                'status' => 'success',
                'message' => 'Active learning materials retrieved successfully',
                'data' => $materials
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve active materials',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
