<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCertificationRequest;
use App\Http\Requests\UpdateCertificationRequest;
use App\Models\Certification;
use App\Traits\ActivityLogTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CertificationController extends Controller implements HasMiddleware
{
    use ActivityLogTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:Certification Index', ['only' => ['index', 'show']]),
            new Middleware('permission:Certification Create', ['only' => ['store']]),
            new Middleware('permission:Certification Update', ['only' => ['update']]),
            new Middleware('permission:Certification Soft Delete', ['only' => ['destroy']]),
            new Middleware('permission:Certification Force Delete', ['only' => ['forceDelete']]),
            new Middleware('permission:Certification Restore', ['only' => ['restore']]),
            new Middleware('permission:Certification Toggle Active', ['only' => ['toggleActive']]),
            new Middleware('permission:Certification Import', ['only' => ['bulkImport']]),
        ];
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = Certification::query();

            // Search
            if ($request->has('search') && $request->search != '') {
                $query->search($request->search);
            }

            // Active Filter
            if ($request->has('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // Soft Deleted Filter
            if ($request->has('trashed') && $request->trashed == 'true') {
                $query->onlyTrashed();
            }

            $certifications = $query->ordered()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Certifications retrieved successfully',
                'data' => $certifications
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve certifications',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function store(CreateCertificationRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $certification = Certification::create($data);

            DB::commit();

            $this->logActivity('CREATE', 'Certification', "Created certification for: {$certification->full_name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Certification created successfully',
                'data' => $certification
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create certification',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $certification = Certification::find($id);

            if (!$certification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Certification not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Certification retrieved successfully',
                'data' => $certification
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve certification',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(UpdateCertificationRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $certification = Certification::find($id);

            if (!$certification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Certification not found'
                ], 404);
            }

            $data = $request->validated();
            $certification->update($data);

            DB::commit();

            $this->logActivity('UPDATE', 'Certification', "Updated certification for: {$certification->full_name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Certification updated successfully',
                'data' => $certification
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update certification',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $certification = Certification::find($id);

            if (!$certification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Certification not found'
                ], 404);
            }

            $name = $certification->full_name;
            $certification->delete();

            $this->logActivity('DELETE', 'Certification', "Soft deleted certification for: {$name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Certification soft deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete certification',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function restore(string $id)
    {
        try {
            $certification = Certification::onlyTrashed()->find($id);

            if (!$certification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Trashed certification not found'
                ], 404);
            }

            $certification->restore();

            $this->logActivity('RESTORE', 'Certification', "Restored certification for: {$certification->full_name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Certification restored successfully',
                'data' => $certification
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to restore certification',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function forceDelete(string $id)
    {
        try {
            $certification = Certification::withTrashed()->find($id);

            if (!$certification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Certification not found'
                ], 404);
            }

            $name = $certification->full_name;
            $certification->forceDelete();

            $this->logActivity('FORCE_DELETE', 'Certification', "Permanently deleted certification for: {$name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Certification permanently deleted'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to force delete certification',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function toggleActive(string $id)
    {
        try {
            $certification = Certification::find($id);

            if (!$certification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Certification not found'
                ], 404);
            }

            $certification->is_active = !$certification->is_active;
            $certification->save();

            $status = $certification->is_active ? 'activated' : 'deactivated';
            $this->logActivity('TOGGLE_ACTIVE', 'Certification', "Changed status to {$status} for: {$certification->full_name}");

            return response()->json([
                'status' => 'success',
                'message' => "Certification {$status} successfully",
                'data' => $certification
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to toggle status',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();

        $fileHandle = fopen($filePath, 'r');
        $header = fgetcsv($fileHandle);

        if ($header) {
            $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
        }

        $expectedHeader = [
            'full_name', 'starting_date', 'ending_date', 'entrol_number',
            'course_code', 'verification_code', 'certificate_number', 'nic'
        ];

        // Basic header validation
        if (!$header || count(array_intersect($expectedHeader, $header)) !== count($expectedHeader)) {
            fclose($fileHandle);
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid CSV header. Expected: ' . implode(', ', $expectedHeader),
            ], 422);
        }

        DB::beginTransaction();
        $importedCount = 0;
        $errors = [];
        $rowNumber = 1;

        try {
            while (($row = fgetcsv($fileHandle)) !== false) {
                $rowNumber++;
                $data = array_combine($header, $row);

                // Row-level validation
                $validator = Validator::make($data, [
                    'full_name' => 'required|string|max:255',
                    'starting_date' => 'required|date',
                    'ending_date' => 'required|date|after_or_equal:starting_date',
                    'entrol_number' => 'nullable|string|unique:certifications,entrol_number|max:255',
                    'course_code' => 'nullable|string|unique:certifications,course_code|max:255',
                    'verification_code' => 'required|string|unique:certifications,verification_code|max:255',
                    'certificate_number' => 'required|string|unique:certifications,certificate_number|max:255',
                    'nic' => 'required|string|unique:certifications,nic|max:255',
                ]);

                if ($validator->fails()) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'errors' => $validator->errors()->all()
                    ];
                    continue; 
                }

                Certification::create($data);
                $importedCount++;
            }

            if (!empty($errors)) {
                DB::rollBack();
                fclose($fileHandle);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bulk import failed due to validation errors',
                    'errors' => $errors
                ], 422);
            }

            DB::commit();
            fclose($fileHandle);

            $this->logActivity('IMPORT', 'Certification', "Imported {$importedCount} certifications via CSV");

            return response()->json([
                'status' => 'success',
                'message' => "Successfully imported {$importedCount} certifications",
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            if (is_resource($fileHandle)) fclose($fileHandle);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to import certifications',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
