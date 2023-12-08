<?php

namespace App\Policies;

use App\Models\SiteCsvImport as SiteCsvImportModel;
use App\Models\User as UserModel;

class SiteCsvImportPolicy extends Policy
{
    public function read(?UserModel $user, ?SiteCsvImportModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function readAll(?UserModel $user, ?SiteCsvImportModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
