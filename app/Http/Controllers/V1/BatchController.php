<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBatchRequest;
use App\Http\Requests\UpdateBatchRequest;
use App\Models\Batch;
use App\Traits\ActivityLogTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BatchController extends Controller implements HasMiddleware
{
    use ActivityLogTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:Batch Index', ['only' => ['index', 'show']]),
            new Middleware('permission:Batch Create', ['only' => ['store']]),
            new Middleware('permission:Batch Update', ['only' => ['update']]),
            new Middleware('permission:Batch Delete', ['only' => ['destroy']]),
            new Middleware('permission:Batch Toggle Active', ['only' => ['toggleActive']]),
        ];
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = Batch::query();

            // Search
            if ($request->has('search') && $request->search != '') {
                $query->search($request->search);
            }

            // Active Filter
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $batches = $query->ordered()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Batches retrieved successfully',
                'data' => $batches
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve batches',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function store(CreateBatchRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $batch = Batch::create($data);

            DB::commit();

            $this->logActivity('CREATE', 'Batch', "Created batch: {$batch->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Batch created successfully',
                'data' => $batch
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create batch',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $batch = Batch::find($id);

            if (!$batch) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Batch not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Batch retrieved successfully',
                'data' => $batch
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve batch',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(UpdateBatchRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $batch = Batch::find($id);

            if (!$batch) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Batch not found'
                ], 404);
            }

            $data = $request->validated();

            if (isset($data['name']) && empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $batch->update($data);

            DB::commit();

            $this->logActivity('UPDATE', 'Batch', "Updated batch: {$batch->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Batch updated successfully',
                'data' => $batch
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update batch',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $batch = Batch::find($id);

            if (!$batch) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Batch not found'
                ], 404);
            }

            $name = $batch->name;
            $batch->delete();

            $this->logActivity('DELETE', 'Batch', "Deleted batch: {$name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Batch deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete batch',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function toggleActive(string $id)
    {
        try {
            $batch = Batch::find($id);

            if (!$batch) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Batch not found'
                ], 404);
            }

            $batch->is_active = !$batch->is_active;
            $batch->save();

            $status = $batch->is_active ? 'activated' : 'deactivated';
            $this->logActivity('TOGGLE_ACTIVE', 'Batch', "Changed status to {$status} for: {$batch->name}");

            return response()->json([
                'status' => 'success',
                'message' => "Batch {$status} successfully",
                'data' => $batch
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
            $batches = Batch::active()->ordered()->get(['id', 'name', 'slug', 'year']);

            return response()->json([
                'status' => 'success',
                'message' => 'Active batches retrieved successfully',
                'data' => $batches
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve active batches',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
