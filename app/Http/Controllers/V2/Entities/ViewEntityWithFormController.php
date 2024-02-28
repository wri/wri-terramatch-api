<?php

namespace App\Http\Controllers\V2\Entities;

use App\Http\Controllers\Controller;
use App\Models\V2\EntityModel;

class ViewEntityWithFormController extends Controller
{
    public function __invoke(EntityModel $entity)
    {
        $this->authorize('read', $entity);
        return $entity->createSchemaResource();
    }
}
