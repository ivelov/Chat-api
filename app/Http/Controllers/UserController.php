<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function getUser() 
    {
        $user = Auth::user();

        Log::info($user->photo);
        $user->photo = $user->photo? $user->photo : 'storage/avatars/default.png';
        
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
        Log::info($request->photo);

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

        $user->photo = $user->photo? $user->photo : 'storage/avatars/default.png';
        return response()->json($user);
    }
}
