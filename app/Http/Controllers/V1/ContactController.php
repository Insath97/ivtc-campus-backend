<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReplyContactRequest;
use App\Models\Contact;
use App\Mail\ContactReplyMail;
use App\Traits\ActivityLogTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller implements HasMiddleware
{
    use ActivityLogTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:Contact Index', ['only' => ['index', 'show']]),
            new Middleware('permission:Contact Reply', ['only' => ['reply']]),
            new Middleware('permission:Contact Delete', ['only' => ['destroy']]),
        ];
    }

    /**
     * Display a listing of contact messages.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = Contact::with('repliedBy:id,name');

            // Search
            if ($request->has('search') && $request->search != '') {
                $query->search($request->search);
            }

            // Status Filter
            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            $contacts = $query->ordered()->paginate($perPage);

            return response()->json([
                'status'  => 'success',
                'message' => 'Contact messages retrieved successfully',
                'data'    => $contacts
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve contact messages',
                'error'   => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified contact message.
     */
    public function show(string $id)
    {
        try {
            $contact = Contact::with('repliedBy:id,name')->find($id);

            if (!$contact) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Contact message not found'
                ], 404);
            }

            // Mark as seen if pending
            if ($contact->status === 'pending') {
                $contact->update(['status' => 'seen']);
                $this->logActivity('VIEW_MESSAGE', 'Contact', "Message from {$contact->name} was viewed and marked as seen by administrator.");
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Contact message detail retrieved successfully',
                'data'    => $contact
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve contact message',
                'error'   => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Reply to the contact message.
     */
    public function reply(ReplyContactRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $contact = Contact::find($id);

            if (!$contact) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Contact message not found'
                ], 404);
            }

            $contact->update([
                'reply_message' => $request->reply_message,
                'status'        => 'replied',
                'is_replied'    => true,
                'replied_by'    => Auth::id(),
                'replied_at'    => now(),
            ]);

            DB::commit();

            // Send reply email to visitor with try-catch and activity logging
            try {
                Mail::to($contact->email)->send(new ContactReplyMail($contact));
                $this->logActivity('MAIL_SENT', 'Contact', "Reply email sent to visitor: {$contact->email}");
            } catch (\Exception $e) {
                $this->logActivity('MAIL_FAILED', 'Contact', "Failed to send reply email to visitor: {$contact->email}. Error: " . $e->getMessage());
            }

            $this->logActivity('REPLY', 'Contact', "Replied to message from: {$contact->name}");

            return response()->json([
                'status'  => 'success',
                'message' => 'Reply recorded successfully',
                'data'    => $contact->load('repliedBy:id,name')
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to record reply',
                'error'   => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove the specified contact message from storage.
     */
    public function destroy(string $id)
    {
        try {
            $contact = Contact::find($id);

            if (!$contact) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Contact message not found'
                ], 404);
            }

            $name = $contact->name;
            $subject = $contact->subject;
            $contact->delete();

            $this->logActivity('DELETE', 'Contact', "Deleted message from: {$name} (Subject: {$subject})");

            return response()->json([
                'status'  => 'success',
                'message' => 'Contact message deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete contact message',
                'error'   => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
