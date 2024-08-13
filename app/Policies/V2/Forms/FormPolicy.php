<?php

namespace App\Policies\V2\Forms;

use App\Models\V2\Forms\Form as FormModel;
use App\Models\V2\User;
use App\Policies\Policy;

class FormPolicy extends Policy
{
    public function listLinkedFields(?User $user, ?FormModel $model = null): bool
    {
        return  $this->isFullUser($user) || $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function uploadFiles(?User $user, ?FormModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function read(?User $user, ?FormModel $model = null): bool
    {
        return $this->isFullUser($user) || $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }
}
