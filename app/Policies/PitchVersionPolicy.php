<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PitchVersion;

class PitchVersionPolicy extends Policy
{
    public function read(?User $user, ?PitchVersion $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function approve(?User $user, ?PitchVersion $model = null): bool
    {
        $isPending = $model->status == "pending";
        return $this->isVerifiedAdmin($user) && $isPending;
    }

    public function reject(?User $user, ?PitchVersion $model = null): bool
    {
        $isPending = $model->status == "pending";
        return $this->isVerifiedAdmin($user) && $isPending;
    }

    public function delete(?User $user, ?PitchVersion $model = null): bool
    {
        $isPending = $model->status == "pending";
        return $this->isFullUser($user) && $this->isOwner($user, $model) && $isPending;
    }
}
