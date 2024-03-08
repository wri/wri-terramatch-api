<?php

namespace App\Http\Controllers\V2\Entities;

use App\Events\V2\General\EntityDeleteEvent;
use App\Http\Controllers\Controller;
use App\Models\V2\EntityModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminSoftDeleteEntityController extends Controller
{
    public function __invoke(Request $request, EntityModel $entity): JsonResource
    {
        $this->authorize('delete', $entity);
        $entity->delete();
        EntityDeleteEvent::dispatch($request->user(), $entity);
        return $entity->createResource();
    }
}
