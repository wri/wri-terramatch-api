<?php

namespace App\Policies\V2\AuditStatus;

use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\User;
use App\Policies\Policy;

class AuditStatusPolicy extends Policy
{
    public function uploadFiles(?User $user, ?AuditStatus $auditStatus): bool
    {
        return $user->email_address == $auditStatus->created_by;
    }
}
