<?php

namespace App\Policies;

use App\Models\SubmissionMediaUpload as SubmissionMediaUploadModel;
use App\Models\V2\User as UserModel;

class SubmissionMediaUploadPolicy extends Policy
{
    public function create(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }

    public function delete(?UserModel $user, ?SubmissionMediaUploadModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function download(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }
}
