<?php

namespace App\Policies;

use App\Models\SiteSubmissionDisturbance as SiteSubmissionDisturbanceModel;
use App\Models\V2\User as UserModel;

class SiteSubmissionDisturbancePolicy extends Policy
{
    public function delete(?UserModel $user, ?SiteSubmissionDisturbanceModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function update(?UserModel $user, ?SiteSubmissionDisturbanceModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
