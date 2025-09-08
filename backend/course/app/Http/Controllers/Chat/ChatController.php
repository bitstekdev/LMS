<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\MessageThread;
use App\Models\User;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function message()
    {
        return view('frontend.my-courses.message');
    }

    public function new_message()
    {
        return view('frontend.my-courses.new_message_form');
    }

    public function chat($receiver, $product = null)
    {
        $user_id = auth('web')->id();

        $messageThrade = MessageThread::where(function ($query) use ($receiver, $user_id) {
            $query->where('sender_id', $receiver)->where('reciver_id', $user_id);
        })->orWhere(function ($query) use ($receiver, $user_id) {
            $query->where('sender_id', $user_id)->where('reciver_id', $receiver);
        })->first();

        $receiver_data = User::findOrFail($receiver);

        $messages = $messageThrade
            ? Chat::where('message_thrade', $messageThrade->id)->orderByDesc('id')->limit(20)->get()
            : [];

        if ($messageThrade) {
            Chat::where('message_thrade', $messageThrade->id)
                ->where('reciver_id', $user_id)
                ->where('read_status', 0)
                ->update(['read_status' => 1]);
        }

        $product_url = $product ? url("/product/view/{$product}") : null;
        $previousChatList = MessageThread::where('reciver_id', $user_id)
            ->orWhere('sender_id', $user_id)
            ->orderByDesc('id')->get();

        return view('frontend.chat.index', compact('receiver_data', 'messages', 'previousChatList', 'product_url', 'product'));
    }

    public function chat_save(Request $request)
    {
        $receiver_id = $request->reciver_id;
        $user_id = auth('web')->id();

        $messageThrade = MessageThread::firstOrCreate(
            ['sender_id' => min($user_id, $receiver_id), 'reciver_id' => max($user_id, $receiver_id)],
            ['chatcenter' => $request->messagecenter]
        );

        $chat = new Chat([
            'reciver_id' => $receiver_id,
            'sender_id' => $user_id,
            'chatcenter' => $request->messagecenter,
            'message' => $request->message,
            'message_thrade' => $messageThrade->id,
            'file' => '1',
        ]);
        $chat->save();

        if (is_array($request->multiple_files) && $request->multiple_files[0] != null) {
            $rules = ['multiple_files.*' => 'mimes:jpeg,jpg,png,gif,jfif,mp4,mov,wmv,mkv,webm,avi'];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['validationError' => $validator->errors()], 422);
            }

            foreach ($request->multiple_files as $media_file) {
                $file_name = random(40);
                $file_extension = strtolower($media_file->getClientOriginalExtension());
                $path = in_array($file_extension, ['avi', 'mp4', 'webm', 'mov', 'wmv', 'mkv'])
                        ? 'uploads/chat/videos/'.$file_name.'.'.$file_extension
                        : 'uploads/chat/images/'.$file_name;

                $file_type = in_array($file_extension, ['avi', 'mp4', 'webm', 'mov', 'wmv', 'mkv']) ? 'video' : 'image';

                app(FileUploaderService::class)->upload($media_file, $path, 1000, null, $file_type === 'image' ? 300 : null);
            }
        }

        $page_data['message'] = Chat::where('message_thrade', $messageThrade->id)->orderByDesc('id')->limit(1)->get();

        return view('frontend.my-courses.message', $page_data);
    }

    public function chat_load()
    {
        $user_id = auth('web')->id();
        $chats = MessageThread::where('sender_id', $user_id)
            ->orWhere('reciver_id', $user_id)
            ->orderByDesc('id')->get();

        return view('frontend.chat.chat_list', compact('chats'));
    }

    public function chat_read_option()
    {
        $user_id = auth('web')->id();
        Chat::where('reciver_id', $user_id)->where('read_status', 0)->update(['read_status' => 1]);

        return response()->json(['status' => 'read']);
    }

    public function search_chat(Request $request)
    {
        $keyword = $request->input('keyword');
        $users = User::where('name', 'LIKE', "%$keyword%")->get();

        return view('frontend.chat.search_result', compact('users'));
    }

    public function remove_chat($id)
    {
        $chat = Chat::findOrFail($id);
        if ($chat->sender_id == auth('web')->id()) {
            $chat->delete();

            return redirect()->back()->with('success', 'Message deleted');
        }

        return redirect()->back()->with('error', 'Unauthorized');
    }

    public function react_chat(Request $request)
    {
        $chat = Chat::findOrFail($request->chat_id);
        $chat->reaction = $request->reaction;
        $chat->save();

        return response()->json(['success' => true, 'reaction' => $chat->reaction]);
    }
}
