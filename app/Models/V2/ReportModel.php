<?php

namespace App\Models\V2;

use App\Models\V2\Tasks\Task;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string framework_key
 * @property int id
 * @property Task task
 */
interface ReportModel
{
    public function nothingToReport();

    public function updateInProgress();

    public function approve($feedback = NULL);

    public function needsMoreInformation($feedback, $feedbackFields): void;

    public function awaitingApproval();

    public function createResource(): JsonResource;

    public function createSchemaResource(): JsonResource;

    public function getLinkedFieldsConfig();
}
