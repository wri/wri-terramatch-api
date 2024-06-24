<?php

namespace App\Models\V2\UpdateRequests;

interface ApprovalFlow
{
    public function submitForApproval(): void;

    public function approve($feedback): void;

    public function needsMoreInformation($feedback, $feedbackFields): void;
}
