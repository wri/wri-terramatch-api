<?php

namespace App\Http\Controllers\V2\Nurseries;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Nurseries\NurseyWithSchemaResource;
use App\Models\V2\Action;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmitNurseryWithFormController extends Controller
{
    public function __invoke(Nursery $nursery, Request $request)
    {
        $this->authorize('submit', $nursery);

        $form = Form::where('model', Nursery::class)
            ->where('framework_key', $nursery->framework_key)
            ->first();

        if (empty($form)) {
            return new JsonResponse('No nursery form schema found for this framework.', 404);
        }

        $updateRequest = $nursery->updateRequests()
            ->whereIn('status', [
                UpdateRequest::STATUS_AWAITING_APPROVAL,
                UpdateRequest::STATUS_REQUESTED,
                UpdateRequest::STATUS_DRAFT,
                UpdateRequest::STATUS_NEEDS_MORE_INFORMATION])
            ->first();

        if (! empty($updateRequest)) {
            $updateRequest->update([ 'status' => UpdateRequest::STATUS_AWAITING_APPROVAL ]);
            $nursery->update([ 'update_request_status' => UpdateRequest::STATUS_AWAITING_APPROVAL ]);

            Action::where('targetable_type', UpdateRequest::class)
                ->where('targetable_id', $updateRequest->id)
                ->delete();

            Action::where('targetable_type', $updateRequest->updaterequestable_type)
                ->where('targetable_id', $updateRequest->updaterequestable_id)
                ->delete();
        } else {
            $nursery->status = Nursery::STATUS_AWAITING_APPROVAL;
            $nursery->save();

            Action::where('targetable_type', Nursery::class)
                ->where('targetable_id', $nursery->id)
                ->delete();
        }

        return new NurseyWithSchemaResource($nursery, ['schema' => $form]);
    }
}
