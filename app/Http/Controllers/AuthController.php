<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserMakeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(UserMakeRequest $request)
    {
        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        event(new Registered($user));

        Auth::login($user);

        $user->photo = 'storage/avatars/default.png';

        $token = $user->createToken('API Token')->accessToken;
        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = User::findOrFail(Auth::user()->id);

            $user->photo = $user->photo();

            $token = $user->createToken('API Token')->accessToken;
            return response(['token' => $token, 'user' => $user]);
        }
        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        return response(null);
    }

}
