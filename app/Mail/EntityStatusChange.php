<?php

namespace App\Mail;

use App\Models\V2\EntityModel;
use App\Models\V2\ReportModel;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Support\Collection;

class EntityStatusChange extends Mail
{
    private EntityModel $entity;

    public function __construct(EntityModel $entity)
    {
        $this->entity = $entity;

        $this->subject = $this->getSubject();
        $this->title = $this->subject;
        $this->body = $this->getBodyParagraphs()->join('<br><br>');
        $this->link = $this->entity->getViewLinkPath();
        $this->cta = 'View ' . $this->getEntityTypeName();
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

    private function getSubject(): string
    {
        return match ($this->getEntityStatus()) {
            EntityStatusStateMachine::APPROVED =>
                'Your ' . $this->getEntityTypeName() . ' Has Been Approved',
            EntityStatusStateMachine::NEEDS_MORE_INFORMATION =>
                'There is More Information Requested About Your ' . $this->getEntityTypeName(),
            default => '',
        };
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

        return str_replace("\n", "<br>", $feedback);
    }

    private function getBodyParagraphs(): Collection
    {
        $paragraphs = collect();
        if ($this->entity instanceof ReportModel) {
            $paragraphs->push('Thank you for submitting your ' .
                $this->entity->parentEntity()->pluck('name')->first() .
                ' report.');
        } else {
            $paragraphs->push('Thank you for submitting your ' .
                strtolower($this->getEntityTypeName()) .
                ' information for ' .
                $this->entity->name .
                '.');
        }

        $paragraphs->push(match ($this->getEntityStatus()) {
            EntityStatusStateMachine::APPROVED => [
                'The information has been reviewed by your project manager and has been approved.',
                $this->getFeedback(),
            ],
            EntityStatusStateMachine::NEEDS_MORE_INFORMATION => [
                'The information has been reviewed by your project manager and they would like to see the following updates:',
                $this->getFeedback() ?? '(No feedback)'
            ],
            default => null
        });

        $paragraphs->push('If you have any additional questions please reach out to your project manager or to info@terramatch.org');

        return $paragraphs->flatten()->filter();
    }
}