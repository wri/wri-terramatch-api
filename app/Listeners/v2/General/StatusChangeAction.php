<?php

namespace App\Listeners\v2\General;

use App\Events\V2\General\EntityStatusChangeEvent;
use App\Models\V2\Action;
use App\Models\V2\Projects\Project;

//use App\Http\Resources\V2\User\ActionResource;

class StatusChangeAction
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\V2\General\EntityStatusChangeEvent  $event
     * @return void
     */
    public function handle(EntityStatusChangeEvent $event)
    {
        $this->removePreviousActions($event->entity);

        if (data_get($event->entity, 'status') != 'awaiting-approval') {
            Action::create([
                'status' => Action::STATUS_PENDING,
                'targetable_type' => get_class($event->entity),
                'targetable_id' => $event->entity->id,
                'type' => Action::TYPE_NOTIFICATION,
                'title' => $event->title,
                'sub_title' => $event->subTitle,
                'text' => $event->text,
                'organisation_id' => $this->getOrganisationId($event->entity),
                'project_id' => $this->getProjectId($event->entity),
            ]);
        }
    }

    private function getOrganisationId($entity): int
    {
        if ($entity->organisation) {
            return $entity->organisation->id;
        }

        return  $entity->project->organisation->id;
    }

    private function getProjectId($entity): ?int
    {
        if (get_class($entity) == Project::class) {
            return $entity->id;
        }

        if ($entity->project) {
            return $entity->project->id;
        }

        return null;
    }

    private function removePreviousActions($entity)
    {
        Action::where('type', Action::TYPE_NOTIFICATION)
            ->where('targetable_type', get_class($entity))
            ->where('targetable_id', $entity->id)
            ->delete();
    }
}
