<?php

namespace App\Models\V2;

use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\Models\V2\UpdateRequests\ApprovalFlow;

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

    public function isEditable(): bool;

    public function getViewLinkPath(): string;
}
