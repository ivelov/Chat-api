<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                'muted' => $chat->pivot->muted
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

    private function isChatMuted(int $chatId, $userId)
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

        //Get chats-users relations with chosen users
        $chats_users = DB::table('chat_user')->join('users', 'users.id', '=', 'chat_user.user_id')
            ->whereIn('users.email', [$user->email, $user2->email])->get();

        //Create new chat if the chat does not exist
        if ($chats_users->count() > 0) {
            $chat = Chat::findOrFail($chats_users[0]->chat_id);
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
}
