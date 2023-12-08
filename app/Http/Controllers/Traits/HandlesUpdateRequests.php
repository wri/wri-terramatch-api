<?php

namespace App\Http\Controllers\Traits;

use App\Http\Resources\V2\UpdateRequests\UpdateRequestResource;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Support\Facades\Auth;

trait HandlesUpdateRequests
{
    protected function handleUpdateRequest($entity, array $answers): UpdateRequestResource
    {
        $updateRequest = $entity->updateRequests()
            ->whereIn('status', [
                UpdateRequest::STATUS_AWAITING_APPROVAL,
                UpdateRequest::STATUS_REQUESTED,
                UpdateRequest::STATUS_DRAFT,
                UpdateRequest::STATUS_NEEDS_MORE_INFORMATION])
            ->first();

        if (! empty($updateRequest)) {
            $updateRequest->content = array_merge($updateRequest->content, $answers);
            $updateRequest->status = UpdateRequest::STATUS_AWAITING_APPROVAL;
            $updateRequest->save();
        } else {
            $updateRequest = UpdateRequest::create([
                'organisation_id' => $entity->organisation ? $entity->organisation->id : $entity->project->organisation_id,
                'project_id' => $entity->project ? $entity->project->id : $entity->id,
                'created_by_id' => Auth::user()->id,
                'framework_key' => $entity->framework_key,
                'updaterequestable_type' => get_class($entity),
                'updaterequestable_id' => $entity->id,
                'status' => UpdateRequest::STATUS_AWAITING_APPROVAL,
                'content' => $answers,
            ]);
        }

        $entity->update_request_status = $updateRequest->status;
        $entity->save();

        return new UpdateRequestResource($updateRequest);
    }
}
