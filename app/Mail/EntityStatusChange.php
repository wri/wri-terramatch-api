<?php

namespace App\Mail;

use App\Models\V2\EntityModel;
use App\Models\V2\ReportModel;
use App\StateMachines\EntityStatusStateMachine;

class EntityStatusChange extends I18nMail
{
    private EntityModel $entity;

    public function __construct(EntityModel $entity, $user)
    {
        parent::__construct($user);
        $this->entity = $entity;

        if ($this->getEntityStatus() == EntityStatusStateMachine::APPROVED) {
            $this->setSubjectKey('entity-status-change.subject-approved')
                ->setTitleKey('entity-status-change.subject-approved');
        }
        if ($this->getEntityStatus() == EntityStatusStateMachine::NEEDS_MORE_INFORMATION) {
            $this->setSubjectKey('entity-status-change.subject-needs-more-information')
                ->setTitleKey('entity-status-change.subject-needs-more-information');
        }

        if ($this->entity instanceof ReportModel) {
            if ($this->getEntityStatus() == EntityStatusStateMachine::APPROVED) {
                $this->setBodyKey('entity-status-change.body-report-approved');
            }
            if ($this->getEntityStatus() == EntityStatusStateMachine::NEEDS_MORE_INFORMATION) {
                $this->setBodyKey('entity-status-change.body-report-needs-more-information');
            }
        } else {
            if ($this->getEntityStatus() == EntityStatusStateMachine::APPROVED) {
                $this->setBodyKey('entity-status-change.body-entity-approved');
            }
            if ($this->getEntityStatus() == EntityStatusStateMachine::NEEDS_MORE_INFORMATION) {
                $this->setBodyKey('entity-status-change.body-entity-needs-more-information');
            }
        }
        $this->setParams(['{entityTypeName}' => $this->getEntityTypeName(),
            '{lowerEntityTypeName}' => strtolower($this->getEntityTypeName()),
            '{parentEntityName}' => $this->entity->parentEntity()->pluck('name')->first(),
            '{entityName}' => $this->entity->name,
            '{feedback}' => $this->getFeedback() ?? '(No feedback)'])
            ->setCta('entity-status-change.cta');
        $this->link = $this->entity->getViewLinkPath();
        $this->transactional = true;
    }

    private function getEntityTypeName(): string
    {
        if ($this->entity instanceof ReportModel) {
            return 'Report';
        } else {
            return explode_pop('\\', get_class($this->entity));
        }
    }

    private function getEntityStatus(): ?string
    {
        if ($this->entity->status == EntityStatusStateMachine::NEEDS_MORE_INFORMATION ||
            $this->entity->update_request_status == EntityStatusStateMachine::NEEDS_MORE_INFORMATION) {
            return EntityStatusStateMachine::NEEDS_MORE_INFORMATION;
        }

        if ($this->entity->status == EntityStatusStateMachine::APPROVED) {
            return EntityStatusStateMachine::APPROVED;
        }

        return null;
    }

    private function getFeedback(): ?string
    {
        if ($this->entity->update_request_status == EntityStatusStateMachine::APPROVED ||
            $this->entity->update_request_status == EntityStatusStateMachine::NEEDS_MORE_INFORMATION) {
            $feedback = $this
                ->entity
                ->updateRequests()
                ->orderBy('updated_at', 'DESC')
                ->first()
                ->feedback;
        } else {
            $feedback = $this->entity->feedback;
        }

        if (empty($feedback)) {
            return null;
        }

        return str_replace("\n", '<br>', $feedback);
    }
}
