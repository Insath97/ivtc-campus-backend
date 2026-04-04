<?php

namespace App\Http\Controllers\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateContactRequest;
use App\Models\Contact;
use App\Models\SystemSetting;
use App\Mail\ContactSubmittedMail;
use App\Traits\ActivityLogTrait;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class PublicContactController extends Controller
{
    use ActivityLogTrait;
    /**
     * Store a newly created contact message.
     */
    public function store(CreateContactRequest $request)
    {
        try {
            $data = $request->validated();
            $data['status'] = 'pending';

            $contact = Contact::create($data);

            // Determine recipient based on settings: Use specialized email if enabled, otherwise fallback to default config email.
            $isEnabled = SystemSetting::getValue('enable_contact_notification', '1');
            $officialEmail = SystemSetting::getValue('contact_notification_email');
            $defaultEmail = config('mail.from.address');

            $recipient = ($isEnabled == '1' && !empty($officialEmail)) ? $officialEmail : $defaultEmail;

            try {
                Mail::to($recipient)->send(new ContactSubmittedMail($contact));
                $this->logActivity('MAIL_SENT', 'Contact', "Notification email sent to ({$recipient}) for message from: {$contact->name}");
            } catch (\Exception $e) {
                $this->logActivity('MAIL_FAILED', 'Contact', "Failed to send notification email to ({$recipient}) for message from: {$contact->name}. Error: " . $e->getMessage());
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Thank you for your message! We will get back to you soon.',
                'data'    => $contact
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to send message',
                'error'   => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
