<?php

namespace App\Http\Controllers;

use App\Events\NewMessageEvent;
use App\Http\Requests\MessageCreateRequest;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function store(int $chatId, MessageCreateRequest $request)
    {
        $user = Auth::user();

        $data = [
            'chat_id' => $chatId,
            'message' => $request->message,
            'user_id' => $user->id,
        ];

        if ($request->attachment) {
            $hashName = Str::random(40) . '.' . $request->attachment->getClientOriginalExtension();
            $request->attachment->move(public_path('/storage/attachments/'), $hashName);
            $data['attachment'] = 'storage/attachments/' . $hashName;
            $data['attachment_type'] = $request->attachment_type;
        }

        $message = Message::create($data);

        broadcast(new NewMessageEvent($user->id, $chatId, $message->message, $message->attachment_type, $message->attachment))->toOthers();

        return response()->json($message);
    }

    public function markAsRead(int $chatId)
    {
        $user = Auth::user();
        Message::where('chat_id', $chatId)->whereNot('user_id', $user->id)->update(['read' => true]);

        return response(null);
    }
}
