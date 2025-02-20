<?php

namespace App\Policies\V2;

use App\Models\V2\ImpactStory;
use App\Models\V2\User;
use App\Policies\Policy;

class ImpactStoryPolicy extends Policy
{
    public function readAll(?User $user): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $this->isUser($user);
    }

    public function read(?User $user, ImpactStory $impactStory): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $this->isUser($user) || $this->isOwner($user, $impactStory);
    }

    public function create(?User $user): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $user->can('impact-stories-manage');
    }

    public function update(?User $user, ImpactStory $impactStory): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $user->can('impact-stories-manage') || $this->isOwner($user, $impactStory);
    }

    public function delete(?User $user, ImpactStory $impactStory): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $user->can('impact-stories-manage');
    }

    public function uploadFiles(?User $user, ImpactStory $impactStory): bool
    {
        return $this->isVerifiedAdmin($user)
            || $this->isTerrafundAdmin($user)
            || $user->can('impact-stories-manage');
    }
}
