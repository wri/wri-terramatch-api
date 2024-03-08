<?php

namespace App\Models\V2;

use App\Models\V2\Tasks\Task;

/**
 * @property Task task
 */
interface ReportModel extends EntityModel
{
    public function nothingToReport();

    public function updateInProgress();
}
