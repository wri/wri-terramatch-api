<?php

namespace App\Policies\V2\Forms;

use App\Models\V2\Forms\FormQuestionOption as FormQuestionOptionModel;
use App\Models\V2\User;
use App\Policies\Policy;

class FormQuestionOptionPolicy extends Policy
{
    public function uploadFiles(?User $user, ?FormQuestionOptionModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }
}
