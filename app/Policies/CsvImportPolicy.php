<?php

namespace App\Policies;

use App\Models\CsvImport as CsvImportModel;
use App\Models\User as UserModel;

class CsvImportPolicy extends Policy
{
    public function read(?UserModel $user, ?CsvImportModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function readAll(?UserModel $user, ?CsvImportModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
