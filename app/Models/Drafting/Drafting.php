<?php

namespace App\Models\Drafting;

use App\Models\DueSubmission;

abstract class Drafting implements DraftingInterface
{
    public static function draftSubmissionHasBeenCompleted(?Object $draft)
    {
        return DueSubmission::where('id', $draft->due_submission_id)
            ->where('is_submitted', true)
            ->exists();
    }
}
