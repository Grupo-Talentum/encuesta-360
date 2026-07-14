<?php

namespace App\Policies;

use App\Models\User;

class SurveySectionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return true;
    }
}
