<?php

namespace App\Http\Controllers\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;

class PublicStaffController extends Controller
{
    /**
     * Display a listing of public active staff members.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = Staff::active();

            // Search
            if ($request->has('search') && $request->search != '') {
                $query->search($request->search);
            }

            $staff = $query->ordered()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Public staff members retrieved successfully',
                'data' => $staff
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve public staff members',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified staff member.
     */
    public function show($id_or_slug)
    {
        try {
            $staff = Staff::active()
                ->where(function ($query) use ($id_or_slug) {
                    $query->where('id', $id_or_slug)
                        ->orWhere('slug', $id_or_slug);
                })
                ->first();

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
}
