<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

abstract class UserEvent
{
    use SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
