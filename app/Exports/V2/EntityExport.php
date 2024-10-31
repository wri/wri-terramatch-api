<?php

namespace App\Exports\V2;

use App\Models\V2\Forms\Form;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
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
            // Skip the boundary_geojson field as it is not a field that should be exported,
            // until being removed from the database.
            if (isset($field['heading']) && $field['heading'] === 'boundary_geojson') {
                continue;
            }
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
            if (isset($field['heading']) && $field['heading'] === 'boundary_geojson') {
                continue;
            }
            $mapped[] = $this->getAnswer($field, $answers, $this->form->framework_key);
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
            $entity->ppc_external_id ?? $entity->old_id ?? $entity->id ?? null,
            $entity->uuid,
        ];

        if (in_array($this->form->type, ['site', 'nursery', 'site-report', 'nursery-report'])) {
            $frontEndUrl = config('app.front_end');
            // Our environment variable definitions are inconsistent.
            if (! Str::endsWith($frontEndUrl, '/')) {
                $frontEndUrl .= '/';
            }
            $mapped[] = $frontEndUrl . 'admin#/' . Str::camel($entity->shortName) . '/' . $entity->uuid . '/show';
        }

        $mapped = array_merge($mapped, [
            $organisation->readable_type ?? null,
            $organisation->name ?? null,
            $entity->project->name ?? null,
            $entity->status ?? null,
            $entity->update_request_status ?? null,
            $entity->due_at ?? null,
        ]);

        if (in_array($this->form->type, ['nursery', 'nursery-report', 'site', 'site-report', 'project-report'])) {
            $mapped[] = $entity->project->ppc_external_id ?? $entity->project->id ?? null;
        }

        if ($this->form->type === 'project-report') {
            $mapped[] = $entity->project->uuid ?? null;
            if($this->form->framework_key === 'ppc') {
                $mapped[] = $entity->seedlings_grown ?? null;
                $mapped[] = $entity->seedlings_grown_to_date ?? null;
            }
        }
        if ($this->form->type === 'nursery-report') {
            $mapped[] = $entity->nursery->old_id ?? ($entity->nursery->id ?? null);
            $mapped[] = $entity->nursery->name ?? null;
        }

        if ($this->form->type === 'site-report') {
            $mapped[] = $entity->site->ppc_external_id ?? $entity->site->id ?? null;
            $mapped[] = $entity->site->name ?? null;
            $sumTreeSpecies = $entity->treeSpecies()->sum('amount');
            $mapped[] = $sumTreeSpecies > 0 ? $sumTreeSpecies : null;
            $mapped[] = $entity->site->trees_planted_count ?? null;
            if($this->form->framework_key === 'ppc') {
                $sumSeeding = $entity->seedings()->sum('amount');
                $mapped[] = $sumSeeding > 0 ? $sumSeeding : null;
                $mapped[] = $entity->site->seeds_planted_count ?? null;
            }
        }

        return $mapped;
    }

    protected function getAttachedHeadingsForEntity(): array
    {
        $initialHeadings = ['id', 'uuid'];

        if (in_array($this->form->type, ['site', 'nursery', 'site-report', 'nursery-report'])) {
            $initialHeadings[] = 'link_to_terramatch';
        }

        $initialHeadings = array_merge($initialHeadings, [
            'organization-readable_type',
            'organization-name',
            'project_name',
            'status',
            'update_request_status',
            'due_date',
        ]);

        if (in_array($this->form->type, ['nursery', 'nursery-report','site', 'site-report', 'project-report'])) {
            $initialHeadings[] = 'project-id';
        }

        if ($this->form->type === 'project-report') {
            $initialHeadings[] = 'project_uuid';
            if($this->form->framework_key === 'ppc') {
                $initialHeadings[] = 'total_seedlings_grown_report';
                $initialHeadings[] = 'total_seedlings_grown';
            }
        }

        if ($this->form->type === 'nursery-report') {
            $initialHeadings = array_merge($initialHeadings, ['nursery-id', 'nursery-name']);
        }

        if ($this->form->type === 'site-report') {
            $initialHeadings = array_merge($initialHeadings, ['site-id', 'site-name', 'total_trees_planted_report', 'total_trees_planted']);
            if($this->form->framework_key === 'ppc') {
                $initialHeadings[] = 'total_seeds_planted_report';
                $initialHeadings[] = 'total_seeds_planted';
            }
        }

        return $initialHeadings;
    }
}
