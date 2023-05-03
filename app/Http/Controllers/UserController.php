<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserSearchRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{
    public function getUser() 
    {
        $user = User::findOrFail(Auth::user()->id);

        $user->photo = $user->photo();
        
        return response()->json($user);
    }

    public function update(int $userId, Request $request)
    {
        $user = User::findOrFail($userId);
        if($user->id !== Auth::user()->id){
            abort(403);
        }
        
        if($request->nickname){
            $user->nickname = $request->nickname;
        }

        if($request->name){
            $user->name = $request->name;
        }

        if($request->lang){
            $user->lang = $request->lang;
        }

        if($request->photo){
            $hashName = Str::random(40) . '.' . $request->photo->getClientOriginalExtension();
            $request->photo->move(public_path('/storage/avatars/'), $hashName);
            if($user->photo){
                File::delete(public_path($user->photo));
            }
            $user->photo = 'storage/avatars/' . $hashName;
        }

        if($user->isDirty()){
            $user->save();
        }

        $user->photo = $user->photo();
        return response()->json($user);
    }

    public function search(UserSearchRequest $request)
    {
        $maxUsers = 30;

        $byName = User::where('name', 'like', '%' . $request->searchText . '%');
        $byNick = User::where('nickname', 'like', '%' . $request->searchText . '%');
        $users = User::where('email', 'like', '%' . $request->searchText . '%')->union($byName)->union($byNick)->limit($maxUsers)->get();

        $data = [];
        foreach ($users as $user) {
            $user->photo = $user->photo();
            $data[] = $user;
        }

        return response()->json($data);
    }
}
