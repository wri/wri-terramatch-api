<?php

namespace App\Resources;

use App\Models\FrameworkInviteCode as FrameworkInviteCodeModel;

class FrameworkInviteCodeResource extends Resource
{
    public function __construct(FrameworkInviteCodeModel $frameworkInviteCode)
    {
        $this->id = $frameworkInviteCode->id;
        $this->code = $frameworkInviteCode->code;
        $this->framework_id = $frameworkInviteCode->framework_id;
        $this->created_at = $frameworkInviteCode->created_at;
        $this->updated_at = $frameworkInviteCode->updated_at;
    }
}
