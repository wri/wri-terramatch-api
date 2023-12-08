<?php

namespace App\Models\Drafting;

interface DraftingInterface
{
    public static function draftSubmissionHasBeenCompleted(?Object $draft);

    public static function transformUploads(Object $data): Object;

    public static function extractUploads(Object $data): array;
}
