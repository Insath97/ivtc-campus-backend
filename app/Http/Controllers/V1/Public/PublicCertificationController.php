<?php

namespace App\Http\Controllers\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicCertificationController extends Controller
{
    /**
     * Verify a certificate using verification code and certificate number.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_code' => 'required_without:certificate_number|nullable|string',
            'certificate_number' => 'required_without:verification_code|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $certification = Certification::where(function($query) use ($request) {
                if ($request->filled('verification_code')) {
                    $query->orWhere('verification_code', $request->verification_code);
                }
                if ($request->filled('certificate_number')) {
                    $query->orWhere('certificate_number', $request->certificate_number);
                }
            })
            ->where('is_active', true)
            ->first();

            if (!$certification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid certificate details. Please check your verification code or certificate number.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Certificate verified successfully.',
                'data' => $certification
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to verify certificate.',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
