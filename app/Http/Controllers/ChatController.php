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

            $chats[] = [
                'avatar' => $user2->photo?'storage/'.$user2->photo:'storage/avatars/default.png',
                'name' => $chatName,
                'last_message' => $lastMessage->message,
                'unread_count' => $unreadMessagesCount
            ];
        }

        return response()->json($chats);
    }

    private function getSecondUser(int $chatId, int $firstUserId)
    {
        $idOfUsersInChat = DB::table('chat_user')->where('chat_id', $chatId)->get();
        foreach ($idOfUsersInChat as $userId) {
            if ($userId != $firstUserId) {
                $user2 = User::findOrFail($userId);
                return $user2;
            }
        }
        return null;
    }

    public function store(Request $request)
    {
        $user1 = Auth::user();
        $user2 = User::where('email', $request->email)->first();

        if(!$user2){
            throw ValidationException::withMessages([
                'email' => 'User with this email does not exist.',
            ]);
        }

        //Get chats with chosen users
        $chats_users = DB::table('chat_user')->join('users', 'users.id', '=', 'chat_user.user_id')
            ->whereIn('users.email', [$user1->email, $user2->email])->get();

        //If chat already exist
        if ($chats_users->count() > 0) {
            $chat = Chat::findOrFail($chats_users[0]->chat_id);
        } else {
            $chat = Chat::create();
            $chat->users()->saveMany([$user1, $user2]);
        }

        $chatName = $user2->nickname ? $user2->nickname : $user2->name;

        return response()->json([
            'messages' => $chat->messages()->orderBy('created_at', 'desc')->limit(100)->get(), 
            'name' => $chatName,
            'avatar' => $user2->photo?'storage/'.$user2->photo:'storage/avatars/default.png',
        ]);
    }
}
