<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserMakeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(UserMakeRequest $request)
    {
        User::where('email', $request->email)->delete();
        $user = User::create($request->all());
        event(new Registered($user));
    }
}
