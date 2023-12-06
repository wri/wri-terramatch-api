<?php

namespace App\Resources;

class PendingResource extends Resource
{
    public function __construct($completedSubmissions, $dueSubmissions)
    {
        $this->completed_submissions = $completedSubmissions;
        $this->due_submissions = $dueSubmissions;
    }
}
