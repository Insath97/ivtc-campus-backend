<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'site_name' => 'IVTC Campus',
            'contact_numbers' => '+94 11 234 5678',
            'official_email' => 'hello@ivtccampus.lk',
            'digital_presence' => 'www.ivtccampus.lk',
            'facebook_url' => "",
            'instagram_url' => "",
            'youtube_url' => "",
            'twitter_url' => "",
            'linkedin_url' => "",
            'office_address' => "",
            'contact_notification_email' => 'hello@ivtccampus.lk',
            'enable_contact_notification' => '1',
            'lms_url' => 'https://lms.ivtccampus.lk/',
        ];

        foreach ($settings as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
