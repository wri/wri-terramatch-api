<?php

namespace App\Models\Reporting;

use App\Models\SiteSubmission;
use Illuminate\Support\Str;

class SiteSubmissionReport extends CustomReport implements CustomReportInterface
{
    public const AVAILABLE_FIELDS = [
        'report_author' => 'Report Author',
        'due_date' => 'Due Date',
        'submitted_date' => 'Date Submitted',
        'tree_species' => 'Tree species (Title and number)',
        'direct_seeding' => 'Direct Seeding (Name and Weight KG)',
        'disturbance_type' => 'Disturbance type',
        'disturbance_intensity' => 'Disturbance intensity',
        'disturbance_extent' => 'Disturbance extent',
        'disturbance_description' => 'Disturbance description',
        'disturbance_details_other' => 'Other disturbance details',
    ];

    public const AVAILABLE_FIlES = [
        'socioeconomic_benefit' => 'Socio economic benefit template doc',
        'images' => 'Images files',
        'document_files' => 'Additional files',
    ];

    public function availableFields(string $relations = 'include'): array
    {
        if ($relations == 'exclude') {
            return  self::AVAILABLE_FIELDS;
        }

        return array_merge(self::AVAILABLE_FIELDS, self::AVAILABLE_FIlES);
    }

    public function generate(): array
    {
        $export = [];
        $submission = $this->exportable;
        $export[] = $this->mapHeaders();
        $row = [];

        foreach ($this->field_list as $field) {
            $field != 'report_author' ?: $row['report_author'] = data_get($submission, 'created_by', '');
            $field != 'due_date' ?: $row['due_date'] = $submission->dueSubmission ? $submission->dueSubmission->due_at->format('Y-m-d H:i') : '-';
            $field != 'submitted_date' ?: $row['submitted_date'] = $submission->created_at->format('Y-m-d H:i');
            $field != 'tree_species' ?: $row['tree_species'] = $this->getTreeSpecies($submission);
            $field != 'direct_seeding' ?: $row['direct_seeding'] = $this->getDirectSeeding($submission);
            $field != 'disturbance_type' ?: $row['disturbance_type'] = $this->getDisturbancePiped($submission, 'disturbance_type');
            $field != 'disturbance_intensity' ?: $row['disturbance_intensity'] = $this->getDisturbancePiped($submission, 'intensity');
            $field != 'disturbance_extent' ?: $row['disturbance_extent'] = $this->getDisturbancePiped($submission, 'extent');
            $field != 'disturbance_description' ?: $row['disturbance_description'] = $this->getDisturbancePiped($submission, 'description');
            $field != 'disturbance_details_other' ?: $row['disturbance_details_other'] = data_get($submission, 'disturbance_information', '');

            $field != 'socioeconomic_benefit' ?: $this->addSocioeconomicBenefitFiles($submission);
            $field != 'images' ?: $this->addImageFiles($submission);
            $field != 'document_files' ?: $this->addDocumentFiles($submission);
        }
        $export[] = $row;

        return $export;
    }

    private function getDirectSeeding(SiteSubmission $submission): string
    {
        $prep = [];
        foreach (data_get($submission, 'directSeedings', []) as $directSeedings) {
            $prep[$directSeedings->name] = isset($prep[$directSeedings->name]) ? $prep[$directSeedings->name] + $directSeedings->weight : $directSeedings->weight;
        }

        $list = [];
        foreach ($prep as $name => $weight) {
            $list[] = "$name ($weight)";
        }

        return implode('|', $list);
    }

    private function getDisturbancePiped(SiteSubmission $submission, string $value): string
    {
        $list = [];
        foreach (data_get($submission, 'disturbances', []) as $disturbances) {
            $list[] = data_get($disturbances, $value, '');
        }

        return implode('|', $list);
    }

    private function getTreeSpecies(SiteSubmission $submission): string
    {
        $prep = [];
        foreach (data_get($submission, 'siteTreeSpecies', []) as $treeSpecies) {
            $prep[$treeSpecies->name] = isset($prep[$treeSpecies->name]) ? $prep[$treeSpecies->name] + $treeSpecies->amount : $treeSpecies->amount;
        }

        $list = [];
        foreach ($prep as $name => $value) {
            $list[] = "$name ($value)";
        }

        return implode('|', $list);
    }

    private function addSocioeconomicBenefitFiles(SiteSubmission $submission): void
    {
        $socioeconomicBenefit = data_get($submission, 'socioeconomicBenefits', null);
        if ($socioeconomicBenefit) {
            $name = $socioeconomicBenefit->name ?? Str::uuid();
            $extension = pathinfo($socioeconomicBenefit->upload, PATHINFO_EXTENSION);
            $this->addAssetFile('Socioeconomic ' . "{$name}.{$extension}", $socioeconomicBenefit->upload);
        }
    }

    private function addImageFiles(SiteSubmission $submission): void
    {
        foreach (data_get($submission, 'mediaUploads', []) as $mediaUploads) {
            $name = $mediaUploads->media_title ?? Str::uuid();
            $extension = pathinfo($mediaUploads->upload, PATHINFO_EXTENSION);
            $this->addAssetFile('Images/' . "{$name}.{$extension}", $mediaUploads->upload);
        }
    }

    private function addDocumentFiles(SiteSubmission $submission): void
    {
        foreach (data_get($submission, 'documentFiles', []) as $documentFile) {
            $name = $documentFile->media_title ?? Str::uuid();
            $extension = pathinfo($documentFile->upload, PATHINFO_EXTENSION);
            $this->addAssetFile('Additional/' . "{$name}.{$extension}", $documentFile->upload);
        }
    }
}
