<?php

namespace App\Exports\V2;

use App\Models\V2\Forms\Form;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FormSubmissionsExport extends BaseExportFormSubmission implements FromQuery, WithHeadings, WithMapping
{
    protected $fieldMap = [];

    protected $submissions;

    protected $form;

    protected $initialHeadings = [
        'Application ID',
        'Organisation ID',
        'Project Pitch IDs',
        'Submission Statuses',
        'Current Stage',
        'Organization Type',
        'Organization Legal Name',
        'Organization WhatsApp Enabled Phone Number',
        'Headquarters Street address',
        'Headquarters street address 2',
        'Headquarters address City',
        'Headquarters address State/Province',
        'Headquarters address Zipcode',
        'Proof of local legal registration, incorporation, or right to operate',
        'Website URL (optional)',
        'Organization Facebook URL(optional)',
        'Organization Instagram URL(optional)',
        'Organization LinkedIn URL(optional)',
        'Upload your organization logo(optional)',
        'Upload a cover photo (optional)',
    ];

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

        $headings = $this->initialHeadings;

        foreach ($this->fieldMap as $field) {
            $headings[] = data_get($field, 'heading', 'unknown');
        }

        foreach ($this->auditFields as $key => $value) {
            $headings[$key] = $value;
        }

        return $headings;
    }

    public function map($formSubmission): array
    {
        $organisation = $formSubmission->organisation;
        $projectPitch = $formSubmission->projectPitch;
        $answers = $formSubmission->getAllAnswers(['organisation' => $organisation, 'project-pitch' => $projectPitch]);
        $fieldMap = $this->fieldMap;

        $mapped = [
            data_get($formSubmission->application, 'uuid'),
            $organisation->uuid,
            data_get($projectPitch, 'uuid'),
            $formSubmission->status,
            data_get($formSubmission->stage, 'name', ''),
            $organisation->readable_type,
            $organisation->name,
            $organisation->phone,
            $organisation->hq_street_1,
            $organisation->hq_street_2,
            $organisation->hq_city,
            $organisation->hq_state,
            $organisation->hq_zipcode,
            $this->handleOrganisationFiles($organisation, 'legal_registration'),
            $organisation->web_url,
            $organisation->facebook_url,
            $organisation->instagram_url,
            $organisation->linkedin_url,
            $this->handleOrganisationFiles($organisation, 'logo'),
            $this->handleOrganisationFiles($organisation, 'cover'),
        ];

        foreach ($fieldMap as $field) {
            $mapped[] = $this->getAnswer($field, $answers, $this->form->framework_key);
        }

        foreach ($this->auditFields as $key => $value) {
            $mapped[] = data_get($formSubmission, $key, '');
        }

        return $mapped;
    }
}
