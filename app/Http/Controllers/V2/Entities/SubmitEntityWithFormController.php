<?php

namespace App\Http\Controllers\V2\Entities;

use App\Http\Controllers\Controller;
use App\Models\V2\Action;
use App\Models\V2\EntityModel;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmitEntityWithFormController extends Controller
{
    public function __invoke(EntityModel $entity, Request $request)
    {
        $this->authorize('submit', $entity);

        if (empty($entity->getForm())) {
            // The form is needed internally for generating the schema resource, so let's blow up early if there
            // isn't one found.
            return new JsonResponse('No form schema found for this framework.', 404);
        }

        /** @var UpdateRequest $updateRequest */
        $updateRequest = $entity->updateRequests()->isUnapproved()->first();
        if (! empty($updateRequest)) {
            $updateRequest->submitForApproval();
            Action::forTarget($updateRequest)->delete();
        } else {
            $entity->submitForApproval();
        }

        Action::forTarget($entity)->delete();

        return $entity->createSchemaResource();
    }
}
