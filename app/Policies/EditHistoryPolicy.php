<?php

namespace App\Policies;

use App\Models\V2\User as UserModel;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class EditHistoryPolicy extends Policy
{
    public function create(?UserModel $user, ?EloquentModel $model = null): bool
    {
        return $this->isVerifiedUser($user);
    }

    public function view(?UserModel $user, ?EloquentModel $model = null): bool
    {
        return  ($this->isVerifiedUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user) ;
    }

    public function update(?UserModel $user, ?EloquentModel $model = null): bool
    {
        return  ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user) ;
    }

    public function list(?UserModel $user, ?EloquentModel $model = null): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user) ;
    }

    public function changeStatus(?UserModel $user, ?EloquentModel $model = null): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user) ;
    }
}
