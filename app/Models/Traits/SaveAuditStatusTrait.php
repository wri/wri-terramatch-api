<?php

namespace App\Models\Traits;

use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Support\Facades\Auth;

trait SaveAuditStatusTrait
{
    public function saveAuditStatus($auditable_type, $auditable_id, $status, $comment, $type = null, $is_active = null, $request_removed = null, $is_submitted = null)
    {
        return AuditStatus::create([
            'auditable_type' => $auditable_type,
            'auditable_id' => $auditable_id,
            'status' => $status,
            'comment' => $comment,
            'date_created' => now(),
            'created_by' => Auth::user()->email_address,
            'type' => $type,
            'is_submitted' => $is_submitted,
            'is_active' => $is_active,
            'first_name' => Auth::user()->first_name,
            'last_name' => Auth::user()->last_name,
            'request_removed' => $request_removed,
        ]);
    }

    public function saveAuditStatusProjectDeveloperSubmit($entity, $updateRequest)
    {
        $changes = $this->getUpdateRequestChange($entity, $updateRequest);
        $this->saveAuditStatus(get_class($entity), $entity->id, $entity->status, 'Awaiting Review: '.$changes->join(', '), 'change-request', true);
    }

    public function saveAuditStatusAdminApprove($data, $entity)
    {
        $comment = $this->getApproveComment($data);
        $this->saveAuditStatus(get_class($entity), $entity->id, $entity->status, $comment, 'change-request-updated', true);
    }

    public function saveAuditStatusAdminMoreInfo($data, $entity)
    {
        $comment = $this->getMoreInfoComment($data, $entity);
        $this->saveAuditStatus(get_class($entity), $entity->id, $entity->status, $comment, 'change-request', true);
    }

    public function saveAuditStatusAdminRestorationInProgress($entity)
    {
        $this->saveAuditStatus(get_class($entity), $entity->id, $entity->status, 'Restoration In Progress', 'change-request-updated', true);
    }

    public function saveAuditStatusAdminSendReminder($entity, $feedback)
    {
        $this->saveAuditStatus(get_class($entity), $entity->id, $entity->status, 'Feedback: '.$feedback, 'reminder-sent', true);
    }

    public function saveAuditStatusProjectDeveloperSubmitDraft($entity)
    {
        if ($entity->status == 'approved' || $entity->status == 'needs-more-information') {
            $type = 'change-request';
            $comment = 'change request updated';
        }
        $this->saveAuditStatus(get_class($entity), $entity->id, $entity->status, $comment ?? 'Updated', $type ?? '-', true);
    }

    private function getApproveComment($data)
    {
        return 'Approve: '.data_get($data, 'feedback');
    }

    private function getMoreInfoComment($data, $entity)
    {
        $feedbackFields = data_get($data, 'feedback_fields', []);
        $feedbackFieldLabels = [];
        foreach ($feedbackFields as $formQuestionUUID) {
            $question = FormQuestion::isUuid($formQuestionUUID)->first();
            if (is_null($question)) {
                continue;
            }
            $entityModelLinkedName = $this->getEntityModelLinkedName($entity);
            $fields = config('wri.linked-fields.models.'.$entityModelLinkedName.'.fields');
            foreach ($fields as $field => $fieldValue) {
                if ($field == $question->linked_field_key) {
                    $feedbackFieldLabels[] = $fieldValue['label'];
                }
            }
        }

        return 'Request More Information on the following fields: '.implode(', ', $feedbackFieldLabels).'. Feedback: '.data_get($data, 'feedback');
    }

    private function getUpdateRequestChange($entity, $updateRequest)
    {
        $changes = [];
        foreach ($updateRequest->content as $formQuestionUUID => $value) {
            if (is_array($value)) {
                continue;
            }
            $question = FormQuestion::isUuid($formQuestionUUID)->first();
            if (is_null($question)) {
                continue;
            }
            $entityModelLinkedName = $this->getEntityModelLinkedName($entity);
            $fields = config('wri.linked-fields.models.'.$entityModelLinkedName.'.fields');
            foreach ($fields as $field => $fieldValue) {
                if ($field == $question->linked_field_key) {
                    $previousValue = $entity[$fieldValue['property']];

                    if ($previousValue != $value) {
                        $changes[] = $fieldValue['label'];
                    }
                }
            }
        }

        return collect($changes);
    }

    private function getEntityModelLinkedName($entity)
    {
        $class = get_class($entity);

        switch ($class) {
            case Site::class:
                return 'site';
            case Project::class:
                return 'project';
            case Nursery::class:
                return 'nursery';
            case SiteReport::class:
                return 'site-report';
            case ProjectReport::class:
                return 'project-report';
            case NurseryReport::class:
                return 'nursery-report';
            default:
                return 'entity';
        }
    }
}
