<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return response(null);
    }

    public function sendVerificationMail(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return response(null);
    }
}
