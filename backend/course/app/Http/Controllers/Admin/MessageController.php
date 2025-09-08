<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MessageController extends Controller
{
    /**
     * Show the messaging interface and mark messages as read.
     */
    public function message($message_thread_code = '')
    {
        $page_data = [];

        $thread = MessageThread::where('code', $message_thread_code)->first();
        $userId = auth('web')->id();

        $contactCount = MessageThread::where('contact_one', $userId)
            ->orWhere('contact_two', $userId)
            ->count();

        if (! $message_thread_code && $contactCount > 0) {
            $latestThreadCode = MessageThread::latest('id')->value('code');

            return redirect()->route('admin.message', $latestThreadCode);
        }

        if ($thread) {
            Message::where('thread_id', $thread->id)
                ->where('read', '!=', 1)
                ->update(['read' => 1]);
        }

        $page_data['thread_code'] = $message_thread_code;
        $page_data['thread_details'] = $thread;

        return view('admin.message.message', $page_data);
    }

    /**
     * Store a new message in a thread.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'sender_id' => 'required|integer|exists:users,id',
            'receiver_id' => 'required|integer|exists:users,id',
            'thread_id' => 'required|integer|exists:message_threads,id',
        ]);

        $data = [
            'message' => $request->message,
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'thread_id' => $request->thread_id,
            'created_at' => now(),
            'read' => null,
        ];

        Message::create($data);
        MessageThread::where('id', $request->thread_id)->update(['updated_at' => now()]);

        $thread_code = MessageThread::find($request->thread_id)?->code;

        Session::flash('success', get_phrase('Your message has been sent successfully.'));

        return redirect()->route('admin.message', ['message_thread' => $thread_code]);
    }

    /**
     * Create a new thread if one doesn't already exist.
     */
    public function thread_store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
        ]);

        $authId = auth('web')->id();
        $receiverId = $request->receiver_id;

        $existingThread = MessageThread::where(function ($query) use ($authId, $receiverId) {
            $query->where('contact_one', $authId)->where('contact_two', $receiverId);
        })->orWhere(function ($query) use ($authId, $receiverId) {
            $query->where('contact_one', $receiverId)->where('contact_two', $authId);
        })->first();

        if ($existingThread) {
            $threadCode = $existingThread->code;
        } else {
            $threadCode = random(20);

            MessageThread::create([
                'contact_one' => $authId,
                'contact_two' => $receiverId,
                'code' => $threadCode,
                'created_at' => now(),
            ]);

            Session::flash('success', get_phrase('Message thread created successfully.'));
        }

        return redirect()->route('admin.message', ['message_thread' => $threadCode]);
    }

    /**
     * AJAX endpoint to search threads by user name or email.
     */
    public function searchThreads(Request $request)
    {
        $authId = auth('web')->id();
        $search = $request->input('search');

        // Find matching users
        $matchingUsers = User::where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->pluck('id')
            ->toArray();

        // Find threads involving the current user and any matching user
        $messageThreads = MessageThread::where(function ($query) use ($matchingUsers, $authId) {
            $query->whereIn('contact_one', $matchingUsers)->where('contact_two', $authId);
        })->orWhere(function ($query) use ($matchingUsers, $authId) {
            $query->whereIn('contact_two', $matchingUsers)->where('contact_one', $authId);
        })->get();

        return view('admin.message.message_left_side_bar', [
            'message_threads' => $messageThreads,
            'search' => $search,
            'thread' => $request->thread,
        ]);
    }
}
