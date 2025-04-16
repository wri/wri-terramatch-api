<?php

namespace App\Http\Controllers\V2\Entities;

use App\Http\Controllers\Controller;
use App\Jobs\V2\SendProjectManagerJob as SendProjectManagerJobs;
use App\Jobs\V2\SendTaskDigestProjectManagerJob as SendTaskDigestProjectManagerJobs;
use App\Models\Traits\SaveAuditStatusTrait;
use App\Models\V2\Action;
use App\Models\V2\EntityModel;
use App\Models\V2\UpdateRequests\UpdateRequest;
use App\StateMachines\ReportStatusStateMachine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\V2\Sites\SiteReport;

class SubmitEntityWithFormController extends Controller
{
    use SaveAuditStatusTrait;

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
        if ($entity->task && $entity->status == ReportStatusStateMachine::APPROVED && $updateRequest->status == 'approved') {
            $this->checkAndDispatchTaskDigest($entity);
        }
        if (! empty($updateRequest)) {
            $updateRequest->submitForApproval();
            $this->saveAuditStatusProjectDeveloperSubmit($entity, $updateRequest);
            Action::forTarget($updateRequest)->delete();
        } else {
            $entity->submitForApproval();
            $this->saveAuditStatusProjectDeveloperSubmitNotUpdateRequest($entity);
        }

        if (get_class($entity) !== SiteReport::class) {
            SendProjectManagerJobs::dispatch($entity);
        }

        Action::forTarget($entity)->delete();

        return $entity->createSchemaResource();
    }

    public function checkAndDispatchTaskDigest($entity)
    {
        $allReports = collect([
            $entity->task->projectReport()->get(),
            $entity->task->siteReports()->get(),
            $entity->task->nurseryReports()->get(),
        ])->flatten(1);
        $otherReports = $allReports->reject(function ($report) use ($entity) {
            return $report['uuid'] === $entity->uuid;
        });
        $otherReportsApproved = $otherReports->filter(function ($report) {
            return $report['status'] === ReportStatusStateMachine::APPROVED;
        });
        if (count($otherReports) == count($otherReportsApproved)) {
            SendTaskDigestProjectManagerJobs::dispatch($entity);
        }
    }
}
