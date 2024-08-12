<?php

namespace App\Policies\Terrafund;

use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\V2\User as UserModel;
use App\Policies\Policy;

class TerrafundDueSubmissionPolicy extends Policy
{
    public function readAllForUser(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }

    public function assignToDraft(?UserModel $user, ?TerrafundDueSubmission $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function updateDueSubmission(?UserModel $user, ?TerrafundDueSubmission $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
