<?php

namespace App\Observers;

use App\Models\Message;
use Illuminate\Support\Facades\File;

class MessageObserver
{
    public function deleted(Message $message)
    {
        if($message->attachment){
            File::delete(public_path($message->attachment));
        }
    }
}
