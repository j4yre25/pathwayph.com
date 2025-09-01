<?php

namespace App\Policies;

use App\Models\User;
use App\Models\JobApplication;

class JobApplicationPolicy
{
    public function update(User $user, JobApplication $app): bool
    {
        return $app->job?->company?->user_id === $user->id;
    }
}