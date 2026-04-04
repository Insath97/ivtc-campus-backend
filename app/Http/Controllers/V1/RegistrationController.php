<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRegistrationStatusRequest;
use App\Models\Registration;
use App\Traits\ActivityLogTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller implements HasMiddleware
{
    use ActivityLogTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:Registration Index', ['only' => ['index', 'show']]),
            new Middleware('permission:Registration Approve', ['only' => ['approve']]),
            new Middleware('permission:Registration Reject', ['only' => ['reject']]),
            new Middleware('permission:Registration Delete', ['only' => ['destroy']]),
        ];
    }

    /**
     * Display a listing of registrations.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = Registration::with(['pathway', 'program']);

            // Search
            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->search($search)
                      ->orWhere('registration_code', 'like', "%{$search}%");
                });
            }

            // Pathway Filter
            if ($request->has('pathway_id') && $request->pathway_id != '') {
                $query->where('pathway_id', $request->pathway_id);
            }

            // Status Filter
            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            $registrations = $query->ordered()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Registrations retrieved successfully',
                'data' => $registrations
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve registrations',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified registration.
     */
    public function show(string $id)
    {
        try {
            $registration = Registration::with(['pathway', 'program'])->find($id);

            if (!$registration) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Registration retrieved successfully',
                'data' => $registration
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve registration',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Approve the registration.
     */
    public function approve(UpdateRegistrationStatusRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $registration = Registration::find($id);

            if (!$registration) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration not found'
                ], 404);
            }

            $registration->status = 'approved';
            if ($request->has('remarks')) {
                $registration->remarks = $request->remarks;
            }
            $registration->save();

            DB::commit();

            $this->logActivity('APPROVE', 'Registration', "Approved registration: {$registration->registration_code} for {$registration->full_name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Registration approved successfully',
                'data' => $registration
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to approve registration',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Reject the registration.
     */
    public function reject(UpdateRegistrationStatusRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $registration = Registration::find($id);

            if (!$registration) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration not found'
                ], 404);
            }

            $registration->status = 'rejected';
            if ($request->has('remarks')) {
                $registration->remarks = $request->remarks;
            }
            $registration->save();

            DB::commit();

            $this->logActivity('REJECT', 'Registration', "Rejected registration: {$registration->registration_code} for {$registration->full_name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Registration rejected successfully',
                'data' => $registration
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reject registration',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove the specified registration from storage.
     */
    public function destroy(string $id)
    {
        try {
            $registration = Registration::find($id);

            if (!$registration) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration not found'
                ], 404);
            }

            $code = $registration->registration_code;
            $name = $registration->full_name;
            $registration->delete();

            $this->logActivity('DELETE', 'Registration', "Deleted registration: {$code} for {$name}");

            return response()->json([
                'status' => 'success',
                'message' => 'Registration deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete registration',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
