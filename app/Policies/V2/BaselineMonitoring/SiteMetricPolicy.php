<?php

namespace App\Policies\V2\BaselineMonitoring;

use App\Models\V2\BaselineMonitoring\SiteMetric;
use App\Models\V2\User as UserModel;
use App\Policies\Policy;

class SiteMetricPolicy extends Policy
{
    public function view(?UserModel $user, ?SiteMetric $model = null): bool
    {
        return  ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function update(?UserModel $user): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function create(?UserModel $user, ?SiteMetric $model = null): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function delete(?UserModel $user, ?SiteMetric $model = null): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }
}
