<?php

namespace App\Models\V2;

use App\Models\V2\Tasks\Task;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Task task
 */
interface ReportModel extends EntityModel
{
    public function nothingToReport();

    public function updateInProgress();

    public function parentEntity(): BelongsTo;
}
