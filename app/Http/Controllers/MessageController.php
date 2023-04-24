<?php

namespace App\Http\Controllers;

use App\Events\NewMessageEvent;
use App\Http\Requests\MessageCreateRequest;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $message = Message::create($data);

        broadcast(new NewMessageEvent($user->id, $chatId, $message->message));

        return response()->json($message);
    }

    public function markAsRead(int $chatId)
    {
        $user = Auth::user();
        Message::where('chat_id', $chatId)->whereNot('user_id', $user->id)->update(['read'=>true]);

        return response(null);
    }
}
