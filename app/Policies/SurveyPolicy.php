<?php

namespace App\Policies;

use App\Models\User;

class SurveyPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return true;
    }
}
