<?php

namespace App\Models\V2;

use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\Models\V2\UpdateRequests\ApprovalFlow;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int id
 * @property string framework_key
 * @property Project project
 * @property Organisation organisation
 */
interface EntityModel extends UpdateRequestableModel, ApprovalFlow
{
    public function getForm(): ?Form;

    public function getFormConfig(): ?array;

    public function updateFromForm(array $formValues): void;

    public function createResource(): JsonResource;

    public function createSchemaResource(): JsonResource;

    public function isEditable(): bool;

    public function dispatchStatusChangeEvent($user): void;

    public function getViewLinkPath(): string;
}
