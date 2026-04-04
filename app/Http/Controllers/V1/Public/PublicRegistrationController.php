<?php

namespace App\Http\Controllers\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicRegistrationRequest;
use App\Models\Registration;
use App\Models\Pathway;
use App\Models\Course;
use App\Models\RegistrationProgram;
use Illuminate\Support\Facades\DB;

class PublicRegistrationController extends Controller
{
    /**
     * Get programs for a specific pathway.
     */
    public function getProgramsByPathway($pathway_id)
    {
        try {
            $pathway = Pathway::active()->find($pathway_id);

            if (!$pathway) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pathway not found'
                ], 404);
            }

            // If Pro Courses, fetch from courses table
            if ($pathway->slug === 'pro-courses') {
                $programs = Course::active()->forRegistration()->ordered()->get(['id', 'name', 'slug']);
                $type = 'course';
            } else {
                // Otherwise, fetch from registration_programs table
                $programs = RegistrationProgram::where('pathway_id', $pathway_id)
                    ->active()
                    ->ordered()
                    ->get(['id', 'name', 'slug']);
                $type = 'program';
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Programs retrieved successfully',
                'data' => [
                    'type' => $type,
                    'programs' => $programs
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve programs',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store a new registration from the public form.
     */
    public function store(PublicRegistrationRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // The Morph Map will handle using 'course'/'program' as types
            $registration = Registration::create($data);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Your application has been submitted successfully! We will contact you soon.',
                'data' => [
                    'id' => $registration->id,
                    'full_name' => $registration->full_name,
                    'registration_code' => $registration->registration_code
                ]
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit registration. Please try again later.',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
