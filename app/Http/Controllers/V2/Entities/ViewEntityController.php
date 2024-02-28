<?php

namespace App\Http\Controllers\V2\Entities;

use App\Http\Controllers\Controller;
use App\Models\V2\EntityModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViewEntityController extends Controller
{
    public function __invoke(Request $request, EntityModel $entity): JsonResource
    {
        $this->authorize('read', $entity);
        return $entity->createResource();
    }
}
