<?php

namespace App\Exports\V2;

use App\Models\V2\Forms\Form;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EntityExport extends BaseExportFormSubmission implements WithHeadings, WithMapping, FromQuery
{
    protected $fieldMap = [];

    protected Builder $query;

    protected $form;

    public function __construct(Builder $query, Form $form)
    {
        $this->query = $query;
        $this->form = $form;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        $this->fieldMap = $this->generateFieldMap($this->form);

        $headings = $this->getAttachedHeadingsForEntity();

        foreach ($this->fieldMap as $field) {
            $headings[] = data_get($field, 'heading', 'unknown') ;
        }

        foreach ($this->auditFields  as $key => $value) {
            $headings[] = $key;
        }

        return $headings;
    }

    public function map($entity): array
    {
        $answers = $entity->getEntityAnswers($this->form);
        $fieldMap = $this->fieldMap;

        $mapped = $this->getAttachedMappedForEntity($entity);

        foreach ($fieldMap as $field) {
            $mapped[] = $this->getAnswer($field, $answers) ;
        }

        foreach ($this->auditFields  as $key => $value) {
            $mapped[] = data_get($entity, $key, '');
        }

        return $mapped;
    }

    protected function getAttachedMappedForEntity($entity): array
    {
        $organisation = $entity->organisation;

        $mapped = [
            $entity->old_id ?? ($entity->id ?? null),
            $entity->uuid,
            $organisation->readable_type ?? null,
            $organisation->name ?? null,
            $entity->project->name ?? null,
            $entity->status ?? null,
            $entity->due_at ?? null
        ];

        if (in_array($this->form->type, ['nursery', 'nursery-report','site', 'site-report', 'project-report'])) {
            $mapped[] = $entity->project->old_id ?? ($entity->project->id ?? null);
        }

        if ($this->form->type === 'project-report') {
            $mapped[] = $entity->project->uuid ?? null;
            if($this->form->framework_key === 'ppc') {
                $mapped[] = $entity->seedlings_grown  ?? null;
            } 
        }
        if ($this->form->type === 'nursery-report') {
            $mapped[] = $entity->nursery->old_id ?? ($entity->nursery->id ?? null);
            $mapped[] = $entity->nursery->name ?? null;
        }

        if ($this->form->type === 'site-report') {
            $mapped[] = $entity->site->old_id ?? ($entity->site->id ?? null);
            $mapped[] = $entity->site->name ?? null;
            $mapped[] = $entity->treeSpecies()->sum('amount') > 0 ? $entity->treeSpecies()->sum('amount') ?? null : null;
            $mapped[] = $entity->seedings()->sum('amount') > 0 ? $entity->seedings()->sum('amount') ?? null : null;
        }

        return $mapped;
    }

    protected function getAttachedHeadingsForEntity(): array
    {
        $initialHeadings = [
            'id',
            'uuid',
            'organization-readable_type',
            'organization-name',
            'project_name',
            'status',
            'due_date'
        ];

        if (in_array($this->form->type, ['nursery', 'nursery-report','site', 'site-report', 'project-report'])) {
            $initialHeadings[] = 'project-id';
        }

        if ($this->form->type === 'project-report') {
            $initialHeadings[] = 'project_uuid';
            if($this->form->framework_key === 'ppc') {
                $initialHeadings[] = 'total_seedlings_grown';
            } 
        }

        if ($this->form->type === 'nursery-report') {
            $initialHeadings = array_merge($initialHeadings, ['nursery-id', 'nursery-name']);
        }

        if ($this->form->type === 'site-report') {
            $initialHeadings = array_merge($initialHeadings, ['site-id', 'site-name', 'total_trees_planted']);
            if($this->form->framework_key === 'ppc') {
                $initialHeadings[] = 'total_seeds_planted';
            } 
        }

        return $initialHeadings;
    }
}
