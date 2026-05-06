<?php

namespace App\Http\Controllers\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Lecturer;
use Illuminate\Http\Request;

class PublicLecturerController extends Controller
{
    /**
     * Display a listing of public active lecturers.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = Lecturer::active();

            // Search
            if ($request->has('search') && $request->search != '') {
                $query->search($request->search);
            }

            $lecturers = $query->ordered()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Public lecturers retrieved successfully',
                'data' => $lecturers
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve public lecturers',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified lecturer.
     */
    public function show($id_or_slug)
    {
        try {
            $lecturer = Lecturer::active()
                ->where(function ($query) use ($id_or_slug) {
                    $query->where('id', $id_or_slug)
                        ->orWhere('slug', $id_or_slug);
                })
                ->first();

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
}
