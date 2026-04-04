<?php

namespace App\Http\Controllers\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class PublicSettingController extends Controller
{
    /**
     * Get public-facing system settings.
     */
    public function index()
    {
        try {
            // Define a whitelist of safe keys to expose publicly
            $whitelist = [
                'site_name',
                'site_logo',
                'site_favicon',
                'contact_numbers',
                'official_email',
                'office_address',
                'digital_presence',
                'facebook_url',
                'instagram_url',
                'youtube_url',
                'twitter_url',
                'linkedin_url',
                'lms_url',
            ];

            $settings = SystemSetting::whereIn('key', $whitelist)
                ->get()
                ->pluck('value', 'key');

            return response()->json([
                'status'  => 'success',
                'message' => 'Public settings retrieved successfully',
                'data'    => $settings
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve settings',
                'error'   => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
