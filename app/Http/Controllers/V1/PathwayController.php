<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePathwayRequest;
use App\Http\Requests\UpdatePathwayRequest;
use App\Models\Pathway;
use App\Traits\ActivityLogTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PathwayController extends Controller implements HasMiddleware
{
    use ActivityLogTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:Pathway Index', ['only' => ['index', 'show']]),
            new Middleware('permission:Pathway Create', ['only' => ['store']]),
            new Middleware('permission:Pathway Update', ['only' => ['update']]),
            new Middleware('permission:Pathway Delete', ['only' => ['destroy']]),
            new Middleware('permission:Pathway Toggle Active', ['only' => ['toggleActive']]),
        ];
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = Pathway::query();

            // Search
            if ($request->has('search') && $request->search != '') {
                $query->search($request->search);
            }

            // Active Filter
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $pathways = $query->ordered()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Pathways retrieved successfully',
                'data' => $pathways
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve pathways',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function store(CreatePathwayRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $pathway = Pathway::create($data);

            DB::commit();

            $this->logActivity('CREATE', 'Pathway', "Created pathway: {$pathway->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Pathway created successfully',
                'data' => $pathway
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create pathway',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $pathway = Pathway::find($id);

            if (!$pathway) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pathway not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pathway retrieved successfully',
                'data' => $pathway
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve pathway',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(UpdatePathwayRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $pathway = Pathway::find($id);

            if (!$pathway) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pathway not found'
                ], 404);
            }

            $data = $request->validated();

            if (isset($data['name']) && empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $pathway->update($data);

            DB::commit();

            $this->logActivity('UPDATE', 'Pathway', "Updated pathway: {$pathway->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Pathway updated successfully',
                'data' => $pathway
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update pathway',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $pathway = Pathway::find($id);

            if (!$pathway) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pathway not found'
                ], 404);
            }

            $name = $pathway->name;
            $pathway->delete();

            $this->logActivity('DELETE', 'Pathway', "Deleted pathway: {$name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Pathway deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete pathway',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function toggleActive(string $id)
    {
        try {
            $pathway = Pathway::find($id);

            if (!$pathway) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pathway not found'
                ], 404);
            }

            $pathway->is_active = !$pathway->is_active;
            $pathway->save();

            $status = $pathway->is_active ? 'activated' : 'deactivated';
            $this->logActivity('TOGGLE_ACTIVE', 'Pathway', "Changed status to {$status} for: {$pathway->name}");

            return response()->json([
                'status' => 'success',
                'message' => "Pathway {$status} successfully",
                'data' => $pathway
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
            $pathways = Pathway::active()->ordered()->get(['id', 'name', 'slug']);

            return response()->json([
                'status' => 'success',
                'message' => 'Active pathways retrieved successfully',
                'data' => $pathways
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve active pathways',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
