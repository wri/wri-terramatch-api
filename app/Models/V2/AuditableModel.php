<?php

namespace App\Models\V2;

interface AuditableModel
{
    public function auditStatuses();

    public function getAuditableNameAttribute();
}
