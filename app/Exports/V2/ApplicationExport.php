<?php

namespace App\Exports\V2;

use App\Models\V2\FundingProgramme;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ApplicationExport extends BaseExportFormSubmission implements WithHeadings, WithMapping, FromQuery
{
    use Exportable;

    protected $fieldMap = [];

    protected Builder $query;

    protected $fundingProgramme;

    protected $forms = [];

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
        'Created At',
        'Updated At',
        'Website URL (optional)',
        'Organization Facebook URL(optional)',
        'Organization Instagram URL(optional)',
        'Organization LinkedIn URL(optional)',
        'Upload your organization logo(optional)',
        'Upload a cover photo (optional)',
    ];

    public function __construct(Builder $query, FundingProgramme $fundingProgramme)
    {
        $this->query = $query;
        $this->fundingProgramme = $fundingProgramme;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        foreach ($this->fundingProgramme->stages as $stage) {
            if (! empty($stage->form)) {
                $this->forms[] = $stage->form;
            }
        }

        $this->forms = array_reverse($this->forms);
        foreach ($this->forms as $form) {
            $this->fieldMap = array_merge($this->fieldMap, $this->generateFieldMap($form));
        }

        $headings = $this->initialHeadings;

        /**
         * Map inputs are split into two
         * This is because they can exceed the CSV character limit for a cell
         * See Jira tickets WRISP-397 & WRM-4960
         * */
        foreach ($this->fieldMap as $field) {
            $headings[] = data_get($field, 'heading', 'unknown');
            if (data_get($field, 'input_type') == 'mapInput') {
                $headings[] = data_get($field, 'heading', 'unknown') . ' (cont)';
            }
        }

        return $headings;
    }

    public function map($application): array
    {
        $submissions = [];
        foreach ($application->formSubmissions as $submission) {
            $submissions[$submission->form_id] = $submission;
        }

        $answers = [];
        foreach ($this->forms as $form) {
            if (! empty($submissions[$form->uuid])) {
                $formSubmission = $submissions[$form->uuid];
                $organisation = $formSubmission->organisation;
                $pitch = $formSubmission->projectPitch;
                $answers = array_merge($answers, $formSubmission->getAllAnswers(['organisation' => $organisation, 'project-pitch' => $pitch]));
            }
        }

        $organisation = $application->organisation;
        $currentSubmission = $application->currentSubmission;

        $mapped = [
            $application->uuid,
            $organisation->uuid,
            $application->formSubmissions->pluck('project_pitch_uuid')->implode('|'),
            $application->formSubmissions->pluck('status')->implode('|'),
            $currentSubmission ? data_get($currentSubmission->stage, 'name', '') : '',
            $organisation->readable_type,
            $organisation->name,
            $organisation->phone,
            $organisation->hq_street_1,
            $organisation->hq_street_2,
            $organisation->hq_city,
            $organisation->hq_state,
            $organisation->hq_zipcode,
            $this->handleOrganisationFiles($organisation, 'legal_registration'),
            $application->created_at,
            $application->updated_at,
            $organisation->web_url,
            $organisation->facebook_url,
            $organisation->instagram_url,
            $organisation->linkedin_url,
            $this->handleOrganisationFiles($organisation, 'logo'),
            $this->handleOrganisationFiles($organisation, 'cover'),
        ];

        /**
         * Map inputs are split into two
         * This is because they can exceed the CSV character limit for a cell
         * See Jira tickets WRISP-397 & WRM-4960
         * */
        foreach ($this->fieldMap as $field) {
            if (data_get($field, 'input_type') == 'mapInput') {
                $mapped[] = substr($this->getAnswer($field, $answers, $this->fundingProgramme->framework_key), 0, 32000);
                $mapped[] = substr($this->getAnswer($field, $answers, $this->fundingProgramme->framework_key), 32000, 32000);
            } else {
                $mapped[] = $this->getAnswer($field, $answers, $this->fundingProgramme->framework_key);
            }
        }

        return $mapped;
    }
}
