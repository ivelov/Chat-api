<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserMakeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(UserMakeRequest $request)
    {
        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['role'] = "poster";
        $user = User::create($data);

        event(new Registered($user));

        Auth::login($user);

        $token = $user->createToken('API Token')->accessToken;
        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
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

    public function getUser() 
    {
        return response()->json(Auth::user());
    }
}
