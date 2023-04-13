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
        $user = User::create($request->all());
        Log::info($user);
        event(new Registered($user));
    }
}
