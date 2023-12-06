<?php

namespace App\Models\Reporting;

use App\Models\Submission;
use Illuminate\Support\Str;

class ProgrammeSubmissionReport extends CustomReport implements CustomReportInterface
{
    public const AVAILABLE_FIELDS = [
        'report_title' => 'Report title',
        'report_author' => 'Report Author',
        'due_date' => 'Due Date',
        'submitted_date' => 'Date Submitted',
        'technical_narrative' => 'Technical narrative',
        'public_narrative' => 'Public narrative',
        'tree_species' => 'Tree species (Title and number)',
    ];

    public const AVAILABLE_FIlES = [
        'tree_species_additional' => 'Additional tree species doc',
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
        $programme = $submission->programme;

        $export[] = $this->mapHeaders();

        $row = [];
        foreach ($this->field_list as $field) {
            $field != 'report_title' ?: $row['report_title'] = data_get($programme, 'name', '');
            $field != 'due_date' ?: $row['due_date'] = $submission->dueSubmission ? $submission->dueSubmission->due_at->format('Y-m-d H:i') : '-';
            $field != 'submitted_date' ?: $row['submitted_date'] = $submission->created_at->format('Y-m-d H:i');
            $field != 'report_author' ?: $row['report_author'] = data_get($submission, 'created_by', '');
            $field != 'technical_narrative' ?: $row['technical_narrative'] = data_get($submission, 'technical_narrative', '');
            $field != 'public_narrative' ?: $row['public_narrative'] = data_get($submission, 'public_narrative', '');
            $field != 'tree_species' ?: $row['tree_species'] = $this->getTreeSpecies($submission);

            $field != 'socioeconomic_benefit' ?: $this->addSocioeconomicBenefitFiles($submission);
            $field != 'tree_species_additional' ?: $this->addAdditionalSpeciesFiles($submission);
            $field != 'images' ?: $this->addImageFiles($submission);
            $field != 'document_files' ?: $this->addDocumentFiles($submission);
        }
        $export[] = $row;

        return $export;
    }

    private function getTreeSpecies(Submission $submission): string
    {
        $prep = [];
        foreach (data_get($submission, 'programmeTreeSpecies', []) as $treeSpecies) {
            $prep[$treeSpecies->name] = isset($prep[$treeSpecies->name]) ? $prep[$treeSpecies->name] + $treeSpecies->amount : $treeSpecies->amount;
        }

        $list = [];
        foreach ($prep as $name => $value) {
            $list[] = "$name ($value)";
        }

        return implode('|', $list);
    }

    private function addSocioeconomicBenefitFiles(Submission $submission): void
    {
        $socioeconomicBenefit = data_get($submission, 'socioeconomicBenefits', null);
        if ($socioeconomicBenefit) {
            $name = $socioeconomicBenefit->name ?? Str::uuid();
            $extension = pathinfo($socioeconomicBenefit->upload, PATHINFO_EXTENSION);
            $this->addAssetFile('Socioeconomic ' . "{$name}.{$extension}", $socioeconomicBenefit->upload);
        }
    }

    private function addAdditionalSpeciesFiles(Submission $submission): void
    {
        foreach (data_get($submission, 'documentFiles', []) as $documentFiles) {
            if ($documentFiles->collection == 'tree_species') {
                $name = $documentFiles->media_title ?? Str::uuid();
                $extension = pathinfo($documentFiles->upload, PATHINFO_EXTENSION);
                $this->addAssetFile('Species/' . "{$name}.{$extension}", $documentFiles->upload);
            }
        }
    }

    private function addImageFiles(Submission $submission): void
    {
        foreach (data_get($submission, 'mediaUploads', []) as $mediaUploads) {
            $name = $mediaUploads->media_title ?? Str::uuid();
            $extension = pathinfo($mediaUploads->upload, PATHINFO_EXTENSION);
            $this->addAssetFile('Images/' . "{$name}.{$extension}", $mediaUploads->upload);
        }
    }

    private function addDocumentFiles(Submission $submission): void
    {
        foreach (data_get($submission, 'documentFiles', []) as $documentFile) {
            $name = $documentFile->media_title ?? Str::uuid();
            $extension = pathinfo($documentFile->upload, PATHINFO_EXTENSION);
            $this->addAssetFile('Additional/' . "{$name}.{$extension}", $documentFile->upload);
        }
    }
}
