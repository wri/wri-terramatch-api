<?php

namespace App\Models\V2;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string framework_key
 * @property int id
 */
interface ReportModel
{
    public function nothingToReport();

    public function approve($feedback = NULL);

    public function needsMoreInformation($feedback, $feedbackFields): void;

    public function awaitingApproval();

    public function createResource(): JsonResource;

    public function createSchemaResource(): JsonResource;

    public function getLinkedFieldsConfig();

    public function getCompletionStatus(): string;
}
