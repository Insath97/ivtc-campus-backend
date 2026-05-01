<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePastPaperRequest;
use App\Http\Requests\UpdatePastPaperRequest;
use App\Models\Pastpaper;
use App\Traits\ActivityLogTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PastPaperController extends Controller implements HasMiddleware
{
    use ActivityLogTrait, FileUploadTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:Past Paper Index', ['only' => ['index', 'show']]),
            new Middleware('permission:Past Paper Create', ['only' => ['store']]),
            new Middleware('permission:Past Paper Update', ['only' => ['update']]),
            new Middleware('permission:Past Paper Soft Delete', ['only' => ['destroy']]),
            new Middleware('permission:Past Paper Force Delete', ['only' => ['forceDelete']]),
            new Middleware('permission:Past Paper Restore', ['only' => ['restore']]),
            new Middleware('permission:Past Paper Toggle Active', ['only' => ['toggleActive']]),
        ];
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = Pastpaper::with(['batch:id,name', 'creator:id,name']);

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

            // Active Filter
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Soft Deleted Filter
            if ($request->has('trashed') && $request->trashed == 'true') {
                $query->onlyTrashed();
            }

            $papers = $query->ordered()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Past papers retrieved successfully',
                'data' => $papers
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve past papers',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function store(CreatePastPaperRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['created_by'] = auth('api')->id();

            // Handle Paper File Upload
            if ($request->hasFile('paper_file')) {
                $filePath = $this->handleFileUpload(
                    $request,
                    'paper_file',
                    null,
                    'past_papers/papers',
                    'paper_batch_' . $data['batch_id'] . '_' . time()
                );

                if ($filePath) {
                    $data['paper_file_path'] = $filePath;
                }
            }

            // Handle Scheme File Upload
            if (isset($data['has_scheme']) && $data['has_scheme'] && $request->hasFile('scheme_file')) {
                $schemePath = $this->handleFileUpload(
                    $request,
                    'scheme_file',
                    null,
                    'past_papers/schemes',
                    'scheme_batch_' . $data['batch_id'] . '_' . time()
                );

                if ($schemePath) {
                    $data['scheme_file_path'] = $schemePath;
                }
            } else {
                $data['scheme_file_path'] = null;
            }

            $pastPaper = Pastpaper::create($data);

            DB::commit();

            $this->logActivity('CREATE', 'Pastpaper', "Created past paper for batch ID: {$pastPaper->batch_id}");

            return response()->json([
                'status' => 'success',
                'message' => 'Past paper created successfully',
                'data' => $pastPaper->load(['batch:id,name', 'creator:id,name'])
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create past paper',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $pastPaper = Pastpaper::with(['batch:id,name', 'creator:id,name'])->find($id);

            if (!$pastPaper) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Past paper not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Past paper retrieved successfully',
                'data' => $pastPaper
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve past paper',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(UpdatePastPaperRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $pastPaper = Pastpaper::find($id);

            if (!$pastPaper) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Past paper not found'
                ], 404);
            }

            $data = $request->validated();
            $batchId = $data['batch_id'] ?? $pastPaper->batch_id;

            // Handle Paper File Replacement
            if ($request->hasFile('paper_file')) {
                $filePath = $this->handleFileUpload(
                    $request,
                    'paper_file',
                    $pastPaper->paper_file_path,
                    'past_papers/papers',
                    'paper_batch_' . $batchId . '_' . time()
                );

                if ($filePath) {
                    $data['paper_file_path'] = $filePath;
                }
            }

            // Handle Scheme Logic
            $hasScheme = $data['has_scheme'] ?? $pastPaper->has_scheme;
            
            if ($hasScheme) {
                // If they provided a new scheme file, replace the old one
                if ($request->hasFile('scheme_file')) {
                    $schemePath = $this->handleFileUpload(
                        $request,
                        'scheme_file',
                        $pastPaper->scheme_file_path,
                        'past_papers/schemes',
                        'scheme_batch_' . $batchId . '_' . time()
                    );
    
                    if ($schemePath) {
                        $data['scheme_file_path'] = $schemePath;
                    }
                }
            } else {
                // If has_scheme is set to false, delete the old file and clear the path
                if ($pastPaper->scheme_file_path) {
                    $this->deleteFile($pastPaper->scheme_file_path);
                    $data['scheme_file_path'] = null;
                }
            }

            $pastPaper->update($data);

            DB::commit();

            $this->logActivity('UPDATE', 'Pastpaper', "Updated past paper for batch ID: {$pastPaper->batch_id}");

            return response()->json([
                'status' => 'success',
                'message' => 'Past paper updated successfully',
                'data' => $pastPaper->load(['batch:id,name', 'creator:id,name'])
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update past paper',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $pastPaper = Pastpaper::find($id);

            if (!$pastPaper) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Past paper not found'
                ], 404);
            }

            $batchId = $pastPaper->batch_id;
            $pastPaper->delete();

            $this->logActivity('DELETE', 'Pastpaper', "Soft deleted past paper for batch ID: {$batchId}");

            return response()->json([
                'status' => 'success',
                'message' => 'Past paper soft deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete past paper',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function restore(string $id)
    {
        try {
            $pastPaper = Pastpaper::onlyTrashed()->find($id);

            if (!$pastPaper) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Trashed past paper not found'
                ], 404);
            }

            $pastPaper->restore();

            $this->logActivity('RESTORE', 'Pastpaper', "Restored past paper for batch ID: {$pastPaper->batch_id}");

            return response()->json([
                'status' => 'success',
                'message' => 'Past paper restored successfully',
                'data' => $pastPaper->load(['batch:id,name', 'creator:id,name'])
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to restore past paper',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function forceDelete(string $id)
    {
        try {
            $pastPaper = Pastpaper::withTrashed()->find($id);

            if (!$pastPaper) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Past paper not found'
                ], 404);
            }

            // Delete associated files if they exist
            if ($pastPaper->paper_file_path) {
                $this->deleteFile($pastPaper->paper_file_path);
            }
            if ($pastPaper->scheme_file_path) {
                $this->deleteFile($pastPaper->scheme_file_path);
            }

            $batchId = $pastPaper->batch_id;
            $pastPaper->forceDelete();

            $this->logActivity('FORCE_DELETE', 'Pastpaper', "Permanently deleted past paper for batch ID: {$batchId}");

            return response()->json([
                'status' => 'success',
                'message' => 'Past paper permanently deleted'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to force delete past paper',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function toggleActive(string $id)
    {
        try {
            $pastPaper = Pastpaper::find($id);

            if (!$pastPaper) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Past paper not found'
                ], 404);
            }

            $pastPaper->is_active = !$pastPaper->is_active;
            $pastPaper->save();

            $status = $pastPaper->is_active ? 'activated' : 'deactivated';
            $this->logActivity('TOGGLE_ACTIVE', 'Pastpaper', "Changed status to {$status} for past paper (Batch ID: {$pastPaper->batch_id})");

            return response()->json([
                'status' => 'success',
                'message' => "Past paper {$status} successfully",
                'data' => $pastPaper->load(['batch:id,name', 'creator:id,name'])
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
            $query = Pastpaper::active();

            if ($request->has('batch_id')) {
                $query->where('batch_id', $request->batch_id);
            }

            $papers = $query->ordered()->get(['id', 'batch_id', 'description', 'paper_file_path', 'has_scheme', 'scheme_file_path']);

            return response()->json([
                'status' => 'success',
                'message' => 'Active past papers retrieved successfully',
                'data' => $papers
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve active past papers',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
