<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\MediaFile;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function index()
    {
        $userId = auth('web')->id();

        $contacts = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->latest()
            ->pluck('sender_id', 'receiver_id')
            ->toArray();

        $conversations = [];

        if (request()->has('inbox') && request()->query('inbox')) {
            $threadCode = request()->query('inbox');

            $conversations = Message::with('thread')
                ->whereHas('thread', fn ($query) => $query->where('code', $threadCode))
                ->get();
        } elseif (request()->has('instructor') && request()->query('instructor')) {
            $instructorId = request()->query('instructor');
            $threadExists = MessageThread::where(function ($query) use ($userId, $instructorId) {
                $query->where('contact_one', $userId)->where('contact_two', $instructorId);
            })->orWhere(function ($query) use ($userId, $instructorId) {
                $query->where('contact_one', $instructorId)->where('contact_two', $userId);
            })->exists();

            if (! $threadExists) {
                $newThreadCode = Str::random(25);
                MessageThread::create([
                    'code' => $newThreadCode,
                    'contact_one' => $userId,
                    'contact_two' => $instructorId,
                ]);

                return redirect()->route('message', ['inbox' => $newThreadCode, 'instructor' => $instructorId]);
            }
        }

        $enrollments = Enrollment::with('course')->where('user_id', $userId)->get();

        $myInstructors = [];
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if ($course) {
                $instructorIds = json_decode($course->instructor_ids, true) ?? [];

                foreach ($instructorIds as $instructorId) {
                    if (! in_array($instructorId, $myInstructors)) {
                        $myInstructors[] = $instructorId;
                    }
                }

                if (! in_array($course->user_id, $myInstructors)) {
                    $myInstructors[] = $course->user_id;
                }
            }
        }

        $page_data = [
            'my_instructor_ids' => $myInstructors,
            'contacts' => array_keys($contacts),
            'message_threads' => MessageThread::where('contact_one', $userId)
                ->orWhere('contact_two', $userId)->get(),
            'conversations' => $conversations,
        ];

        $view_path = 'frontend.'.get_frontend_settings('theme').'.student.message.index';

        return view($view_path, $page_data);
    }

    public function store(Request $request)
    {
        if (empty($request->message) && ! $request->hasFile('media_files')) {
            return redirect()->back()->withErrors(['message' => 'Cannot send an empty message.']);
        }

        $message = Message::create([
            'sender_id' => auth('web')->id(),
            'receiver_id' => $request->receiver_id,
            'thread_id' => $request->thread,
            'message' => $request->message,
            'read' => 0,
        ]);

        MessageThread::where('id', $request->thread)->update([
            'updated_at' => now(),
        ]);

        if ($request->hasFile('media_files')) {
            $threadCode = MessageThread::where('id', $request->thread)->value('code');

            foreach ($request->file('media_files') as $file) {
                $type = explode('/', $file->getClientMimeType())[0];

                if (in_array($type, ['image', 'video'])) {
                    $fileName = Str::random(20).'.'.$file->getClientOriginalExtension();
                    $path = "uploads/message/{$threadCode}/{$fileName}";

                    app(FileUploaderService::class)->upload($file, $path, null, null, $type === 'image' ? 300 : null);

                    MediaFile::create([
                        'chat_id' => $message->id,
                        'file_name' => $fileName,
                        'file_type' => $type,
                    ]);
                }
            }
        }

        Session::flash('success', get_phrase('Message sent successfully.'));

        return redirect()->back();
    }

    public function fetch_message(Request $request)
    {
        $conversations = Message::where('thread_id', $request->thread)->get();

        return view('frontend.default.student.message.body', compact('conversations'));
    }

    public function search_student(Request $request)
    {
        $user = User::where('email', $request->search_mail)->first();
        $view_path = 'frontend.'.get_frontend_settings('theme').'.student.message.search_result';

        return view($view_path, ['user_details' => $user]);
    }

    public function inbox($user_id)
    {
        $authId = auth('web')->id();

        $thread = MessageThread::where(function ($query) use ($authId, $user_id) {
            $query->where('contact_one', $authId)->where('contact_two', $user_id);
        })->orWhere(function ($query) use ($authId, $user_id) {
            $query->where('contact_one', $user_id)->where('contact_two', $authId);
        })->first();

        if ($thread) {
            return redirect()->route('message', ['inbox' => $thread->code]);
        }

        $code = Str::random(20);
        MessageThread::create([
            'code' => $code,
            'contact_one' => $authId,
            'contact_two' => $user_id,
        ]);

        return redirect()->route('message', ['inbox' => $code, 'instructor' => $user_id]);
    }
}
