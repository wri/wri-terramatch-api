<?php

namespace App\Http\Controllers\V2\Entities;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateRequests\StatusChangeRequest;
use App\Models\V2\EntityModel;
use App\Models\V2\Sites\Site;
use Illuminate\Http\JsonResponse;

class AdminStatusEntityController extends Controller
{
    public function __invoke(StatusChangeRequest $request, EntityModel $entity, string $status)
    {
        $data = $request->validated();
        $this->authorize('approve', $entity);

        switch($status) {
            case 'approve':
                $entity->approve(data_get($data, 'feedback'));

                break;

            case 'moreinfo':
                $entity->needsMoreInformation(data_get($data, 'feedback'), data_get($data, 'feedback_fields'));

                break;

            case 'restoration-in-progress':
                if (get_class($entity) === Site::class) {
                    $entity->restorationInProgress();

                    break;
                }

            default:
                return new JsonResponse('status not supported', 401);
        }

        $entity->dispatchStatusChangeEvent($request->user());

        return $entity->createResource();
    }
}
