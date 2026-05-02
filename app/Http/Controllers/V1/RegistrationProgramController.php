<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRegistrationProgramRequest;
use App\Http\Requests\UpdateRegistrationProgramRequest;
use App\Models\RegistrationProgram;
use App\Models\Pathway;
use App\Traits\ActivityLogTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistrationProgramController extends Controller implements HasMiddleware
{
    use ActivityLogTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:Registration Program Index', ['only' => ['index', 'show']]),
            new Middleware('permission:Registration Program Create', ['only' => ['store']]),
            new Middleware('permission:Registration Program Update', ['only' => ['update']]),
            new Middleware('permission:Registration Program Delete', ['only' => ['destroy']]),
            new Middleware('permission:Registration Program Toggle Active', ['only' => ['toggleActive']]),
        ];
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = RegistrationProgram::query()->with('pathway');

            // Search
            if ($request->has('search') && $request->search != '') {
                $query->search($request->search);
            }

            // Pathway Filter
            if ($request->has('pathway_id')) {
                $query->where('pathway_id', $request->pathway_id);
            }

            // Active Filter
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $programs = $query->ordered()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Registration programs retrieved successfully',
                'data' => $programs
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve registration programs',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function store(CreateRegistrationProgramRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            
            $program = RegistrationProgram::create($data);

            DB::commit();

            $this->logActivity('CREATE', 'RegistrationProgram', "Created registration program: {$program->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Registration program created successfully',
                'data' => $program
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create registration program',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $program = RegistrationProgram::with('pathway')->find($id);

            if (!$program) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration program not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Registration program retrieved successfully',
                'data' => $program
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve registration program',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(UpdateRegistrationProgramRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $program = RegistrationProgram::find($id);

            if (!$program) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration program not found'
                ], 404);
            }

            $data = $request->validated();
            $program->update($data);

            DB::commit();

            $this->logActivity('UPDATE', 'RegistrationProgram', "Updated registration program: {$program->name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Registration program updated successfully',
                'data' => $program
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update registration program',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $program = RegistrationProgram::find($id);

            if (!$program) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration program not found'
                ], 404);
            }

            $name = $program->name;
            $program->delete();

            $this->logActivity('DELETE', 'RegistrationProgram', "Deleted registration program: {$name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Registration program deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete registration program',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function toggleActive(string $id)
    {
        try {
            $program = RegistrationProgram::find($id);

            if (!$program) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration program not found'
                ], 404);
            }

            $program->is_active = !$program->is_active;
            $program->save();

            $status = $program->is_active ? 'activated' : 'deactivated';
            $this->logActivity('TOGGLE_ACTIVE', 'RegistrationProgram', "Changed status to {$status} for: {$program->name}");

            return response()->json([
                'status' => 'success',
                'message' => "Registration program {$status} successfully",
                'data' => $program
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
            $programs = RegistrationProgram::active()->ordered()->get(['id', 'pathway_id', 'name', 'slug']);

            return response()->json([
                'status' => 'success',
                'message' => 'Active registration programs retrieved successfully',
                'data' => $programs
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve active programs',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get active programs for a specific pathway.
     */
    public function getByPathway($pathway_id)
    {
        try {
            $programs = RegistrationProgram::query()->where('pathway_id', $pathway_id)
                ->active()
                ->ordered()
                ->get(['id', 'name', 'slug']);

            return response()->json([
                'status' => 'success',
                'message' => 'Programs for pathway retrieved successfully',
                'data' => $programs
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve programs for pathway',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
