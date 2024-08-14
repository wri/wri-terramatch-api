<?php

namespace App\Policies;

use App\Models\DueSubmission as DueSubmissionModel;
use App\Models\V2\User as UserModel;

class DueSubmissionPolicy extends Policy
{
    public function readAllForUser(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }

    public function assignToDraft(?UserModel $user, ?DueSubmissionModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
