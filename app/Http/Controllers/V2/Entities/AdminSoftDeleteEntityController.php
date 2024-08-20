<?php

namespace App\Http\Controllers\V2\Entities;

use App\Events\V2\General\EntityDeleteEvent;
use App\Http\Controllers\Controller;
use App\Models\V2\EntityModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminSoftDeleteEntityController extends Controller
{
    public function __invoke(Request $request, EntityModel $entity): JsonResource
    {
        $this->authorize('delete', $entity);

        Log::info('Soft Delete Entity ' .
            json_encode([
                'entity_type' => get_class($entity),
                'entity_id' => $entity->id,
                'entity_uuid' => $entity->uuid,
                'current_user_id' => Auth::user()->id,
                'current_user_email_address' => Auth::user()->email_address,
            ]));

        $entity->delete();
        EntityDeleteEvent::dispatch($request->user(), $entity);

        return $entity->createResource();
    }
}
