<?php

namespace App\Policies;

use App\Models\User;

class TeamPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return true;
    }
}
