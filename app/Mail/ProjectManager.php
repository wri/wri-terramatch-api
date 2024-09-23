<?php

namespace App\Mail;

use App\Models\V2\EntityModel;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Support\Str;

class ProjectManager extends I18nMail
{
    private EntityModel $entity;

    public function __construct($entity, $user)
    {
        parent::__construct($user);
        $this->entity = $entity;

        if (get_class($entity) === Project::class || get_class($entity) === ProjectReport::class) {
            $this->setSubjectKey('project-manager-project.subject')
                ->setTitleKey('project-manager-project.title')
                ->setBodyKey('project-manager-project.body')
                ->setParams([
                    '{projectName}' => $this->entity->project->name ?? $this->entity->name,
                    '{entityTypeName}' => 'Project',
                ])
                ->setCta('project-manager-project.cta');
            $this->link = $this->getViewLinkEntity(
                $this->entity->project->shortName ?? $this->entity->shortName,
                $this->entity->project->uuid ?? $this->entity->uuid
            );
        }

        if (get_class($entity) === Site::class || get_class($entity) === SiteReport::class) {
            $this->setSubjectKey('project-manager-site.subject')
                ->setTitleKey('project-manager-site.title')
                ->setBodyKey('project-manager-site.body')
                ->setParams([
                    '{projectName}' => $this->entity->project->name,
                    '{entityName}' => $this->entity->site->name ?? $this->entity->name,
                    '{entityTypeName}' => 'Site',
                ])
                ->setCta('project-manager-site.cta');
            $this->link = $this->getViewLinkEntity(
                $this->entity->site->shortName ?? $this->entity->shortName,
                $this->entity->site->uuid ?? $this->entity->uuid
            );
        }

        if (get_class($entity) === Nursery::class || get_class($entity) === NurseryReport::class) {
            $this->setSubjectKey('project-manager-nursery.subject')
                ->setTitleKey('project-manager-nursery.title')
                ->setBodyKey('project-manager-nursery.body')
                ->setParams([
                    '{projectName}' => $this->entity->project->name,
                    '{entityName}' => $this->entity->nursery->name ?? $this->entity->name,
                    '{entityTypeName}' => 'Nursery',
                ])
                ->setCta('project-manager-nursery.cta');
            $this->link = $this->getViewLinkEntity(
                $this->entity->nursery->shortName ?? $this->entity->shortName,
                $this->entity->nursery->uuid ?? $this->entity->uuid
            );
        }
    }

    public function getViewLinkEntity($entity, $uuid)
    {
        return '/admin#/' . Str::camel($entity) . '/' . $uuid . '/show';
    }
}
