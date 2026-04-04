<?php

namespace App\Http\Controllers\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateContactRequest;
use App\Models\Contact;
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

            // Notify administrator with try-catch and activity logging
            try {
                Mail::to(config('mail.from.address'))->send(new ContactSubmittedMail($contact));
                $this->logActivity('MAIL_SENT', 'Contact', "Notification email sent to admin for message from: {$contact->name}");
            } catch (\Exception $e) {
                $this->logActivity('MAIL_FAILED', 'Contact', "Failed to send notification email to admin for message from: {$contact->name}. Error: " . $e->getMessage());
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
