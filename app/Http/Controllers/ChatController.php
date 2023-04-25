<?php

namespace App\Http\Controllers;

use App\Events\NewMessageEvent;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::user()->id);

        $chats = [];
        foreach ($user->chats as $chat) {
            $user2 = $this->getSecondUser($chat->id, $user->id);

            $chatName = $user2->nickname ? $user2->nickname : $user2->name;

            $lastMessage = $chat->messages()->orderBy('created_at', 'desc')->first();

            $unreadMessagesCount = 0;
            //If last message is unread, calc unread count
            if ($lastMessage && $lastMessage->user_id !== $user->id && !$lastMessage->read) {
                $unreadMessagesCount = $chat->messages()
                    ->whereNot('user_id', $user->id)
                    ->where('read', false)
                    ->orderBy('created_at', 'desc')
                    ->limit(100)
                    ->count();
            }

            $chats[$chat->id] = [
                'avatar' => $user2->photo ? $user2->photo : 'storage/avatars/default.png',
                'name' => $chatName,
                'last_message' => $lastMessage ? $lastMessage->message : null,
                'unread_count' => $unreadMessagesCount,
                'muted' => $chat->pivot->muted,
                'messages' => []
            ];
        }

        return response()->json($chats);
    }

    private function getSecondUser(int $chatId, int $firstUserId)
    {
        $idOfUsersInChat = DB::table('chat_user')->where('chat_id', $chatId)->get();
        foreach ($idOfUsersInChat as $userIdRecord) {
            if ($userIdRecord->user_id != $firstUserId) {
                $user2 = User::findOrFail($userIdRecord->user_id);
                return $user2;
            }
        }
        return null;
    }

    private function isChatMuted(int $chatId, int $userId)
    {
        return  DB::table('chat_user')
            ->where('user_id', $userId)
            ->where('chat_id', $chatId)
            ->first()
            ->muted;
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $user2 = User::where('email', $request->email)->first();
        if (!$user2) {
            throw ValidationException::withMessages([
                'email' => 'User with this email does not exist.',
            ]);
        }

        //Get common chat between users
        $chat_user = DB::table('chat_user')->where('user_id', $user2->id)->whereIn('chat_id', function($query) use($user){
            $query->select('chat_id')->from('chat_user')->where('user_id', $user->id);
        })->first();

        //Create new chat if the chat does not exist
        if ($chat_user) {
            $chat = Chat::findOrFail($chat_user->chat_id);
        } else {
            $chat = Chat::create();
            $chat->users()->saveMany([$user, $user2]);
        }

        $chatName = $user2->nickname ? $user2->nickname : $user2->name;

        $messagesRaw = $chat->messages()->orderBy('created_at', 'desc')->limit(101)->get();
        $hasMore = false;
        if ($messagesRaw->count() > 100) {
            $hasMore = true;
        }
        $messages = [];
        foreach ($messagesRaw as $message) {
            $message->fromYou = $message->user_id === $user->id;
            $messages[] = $message;
        }

        return response()->json([
            'id' => $chat->id,
            'messages' => $messages,
            'name' => $chatName,
            'avatar' => $user2->photo ? $user2->photo : 'storage/avatars/default.png',
            'hasMore' => $hasMore,
            'muted' => $this->isChatMuted($chat->id, $user->id),
        ]);
    }

    public function show(int $chatId, Request $request)
    {
        $user = User::findOrFail(Auth::user()->id);

        $chat = Chat::findOrFail($chatId);
        $user2 = $this->getSecondUser($chatId, $user->id);
        $chatName = $user2->nickname ? $user2->nickname : $user2->name;

        $offset = intval($request->query('offset', 0));
        $messagesRaw = $chat->messages()->orderBy('created_at', 'desc')->offset($offset)->limit(101)->get();
        $hasMore = false;
        if ($messagesRaw->count() > 100) {
            $hasMore = true;
        }
        $messages = [];
        foreach ($messagesRaw as $message) {
            $message->fromYou = $message->user_id === $user->id;
            $messages[] = $message;
        }
        
        return response()->json([
            'id' => $chat->id,
            'messages' => $messages,
            'name' => $chatName,
            'muted' => $this->isChatMuted($chatId, $user->id),
            'avatar' => $user2->photo ? $user2->photo : 'storage/avatars/default.png',
            'hasMore' => $hasMore,
        ]);
    }

    private function changeMute(int $chatId, bool $to)
    {
        $user = Auth::user();
        DB::table('chat_user')->where('user_id', $user->id)->where('chat_id', $chatId)->update(['muted' => $to]);
    }

    public function mute(int $chatId)
    {
        $this->changeMute($chatId, true);

        return response(null);
    }

    public function unmute(int $chatId)
    {
        $this->changeMute($chatId, false);

        return response(null);
    }
}
