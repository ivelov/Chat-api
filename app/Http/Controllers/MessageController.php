<?php

namespace App\Http\Controllers;

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

        return response()->json($message);
    }
}
