<?php

namespace App\Policies\V2\BaselineMonitoring;

use App\Models\User as UserModel;
use App\Models\V2\BaselineMonitoring\ProjectMetric;
use App\Policies\Policy;

class ProjectMetricPolicy extends Policy
{
    public function view(?UserModel $user, ?ProjectMetric $model = null): bool
    {
        return  $this->isFullUser($user) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function overview(?UserModel $user, ?ProjectMetric $model = null): bool
    {
        return  $this->isFullUser($user) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function create(?UserModel $user): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function update(?UserModel $user): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function delete(?UserModel $user, ?ProjectMetric $model = null): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function upload(?UserModel $user, ?ProjectMetric $model = null): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function download(?UserModel $user, ?ProjectMetric $model = null): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deleteMedia(?UserModel $user, ?ProjectMetric $model = null): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deleteCoverMedia(?UserModel $user, ?ProjectMetric $model = null): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deleteGalleryMedia(?UserModel $user, ?ProjectMetric $model = null): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deleteSupportMedia(?UserModel $user, ?ProjectMetric $model = null): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }
}
