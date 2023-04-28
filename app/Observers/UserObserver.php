<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\File;

class UserObserver
{
    public function deleted(User $user)
    {
        if($user->photo){
            File::delete(public_path($user->photo));
        }
    }
}
