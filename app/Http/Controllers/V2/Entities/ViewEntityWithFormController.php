<?php

namespace App\Http\Controllers\V2\Entities;

use App\Http\Controllers\Controller;
use App\Models\V2\EntityModel;
use Illuminate\Support\Facades\Log;

class ViewEntityWithFormController extends Controller
{
    public function __invoke(EntityModel $entity)
    {
        Log::info('ViewEntityWithFormController invoked');
        $this->authorize('read', $entity);

        return $entity->createSchemaResource();
    }
}
