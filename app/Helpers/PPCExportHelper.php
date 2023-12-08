<?php

namespace App\Helpers;

use App\Models\MediaUpload;
use App\Models\Programme;
use App\Models\Site;
use App\Models\SiteSubmission;
use App\Models\Submission;
use App\Models\SubmissionMediaUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use League\Csv\Writer;

class PPCExportHelper
{
    public static function generateSitesCsv(Collection $sites): Object
    {
        $header = [
            'Organisation Name', 'Project Name', 'Project ID', 'Site Name', 'Site ID',
            'Description', 'History', 'Establishment Date', 'Expected End Date',
            'Restoration Methods', 'Land Tenures', 'Target survival rate of planted trees (%)',
            'Target % crown cover by year 5 (%)', 'Target survival rate of direct seeding (%)',
            'Expected total of trees per hectare for natural regeneration',
            'Expected number of hectares for natural regeneration', 'Number of existing mature trees',
            'Soil condition', 'Planting pattern', 'Tree species (name)', 'Tree species (amount)', 'Presence of invasives',
            'Seed details',
        ];
        $records = [];

        foreach ($sites as $site) {
            $records[] = [
                $site->programme->organisation->approved_version ? $site->programme->organisation->approved_version->name : 'Unapproved organisation ID ' . $site->programme->organisation_id,
                $site->programme->name,
                $site->programme->id,
                $site->name,
                $site->id,
                $site->description,
                $site->history,
                $site->establishment_date,
                $site->end_date,
                count($site->siteRestorationMethods) ? implode('|', $site->siteRestorationMethods->pluck('name')->toArray()) : '',
                count($site->landTenures) ? implode('|', $site->landTenures->pluck('name')->toArray()) : '',
                $site->aim_survival_rate,
                $site->aim_year_five_crown_cover,
                $site->aim_direct_seeding_survival_rate,
                $site->aim_natural_regeneration_trees_per_hectare,
                $site->aim_natural_regeneration_hectares,
                $site->aim_number_of_mature_trees,
                $site->aim_soil_condition,
                $site->planting_pattern,
                $site->siteTreeSpecies->pluck('name')->implode('|'),
                $site->siteTreeSpecies->pluck('amount')->implode('|'),
                count($site->invasives) ? implode('|', $site->invasives->pluck('name')->toArray()) : '',
                count($site->seedDetails) ? implode('|', $site->seedDetails->pluck('name')->toArray()) : '',
            ];
        }

        $csv = Writer::createFromString();
        $csv->insertOne($header);
        $csv->insertAll($records);

        return $csv;
    }

    public static function generateSiteFilesZip(string $filename, Site $site): \ZipArchive
    {
        $zip = new \ZipArchive();
        $zippedFiles = [];
        $zip->open($filename, \ZipArchive::CREATE);

        // media
        $site->media->each(function ($file) use (&$zip, &$zippedFiles) {
            if (@file_get_contents($file->upload) !== false) {
                $zippedFiles[] = $file->upload;
                $zip->addFromString('Media - ' . basename($file->upload), @file_get_contents($file->upload));
            }
        });

        // additional documents
        $site->getDocumentFileExcludingCollection(['tree_species'])->each(function ($file) use (&$zip, &$zippedFiles) {
            if ($file && @file_get_contents($file->upload) !== false) {
                $zippedFiles[] = $file->upload;
                $zip->addFromString('Additional Document - ' . basename($file->upload), @file_get_contents($file->upload));
            }
        });

        // additional tree species
        $additionalTreeSpecies = $site->getDocumentFileCollection(['tree_species'])->first();
        if ($additionalTreeSpecies && @file_get_contents($additionalTreeSpecies->upload) !== false) {
            $zippedFiles[] = $additionalTreeSpecies->upload;
            $zip->addFromString('Additional Tree Species - ' . basename($additionalTreeSpecies->upload), @file_get_contents($additionalTreeSpecies->upload));
        }

        // stratification
        if ($site->stratification_for_heterogeneity && @file_get_contents($site->stratification_for_heterogeneity) !== false) {
            $zippedFiles[] = $site->stratification_for_heterogeneity;
            $zip->addFromString('Stratification for Hetereogenity - ' . basename($site->stratification_for_heterogeneity), @file_get_contents($site->stratification_for_heterogeneity));
        }

        $zip->close();

        return $zip;
    }

    public static function getProgrammeAndSiteImages(Programme $programme): Object
    {
        $siteIds = $programme->sites->pluck('id');

        $files = MediaUpload::query()
            ->where('programme_id', $programme->id)
            ->orWhereIn('site_id', $siteIds)
            ->get();

        return $files;
    }

    public static function getProgrammeAndSiteSubmissionImages(Programme $programme): Object
    {
        $siteIds = $programme->sites->pluck('id');
        $programmeSubmissionIds = $programme->submissions->pluck('id');
        $siteSubmissionIds = SiteSubmission::whereIn('site_id', $siteIds)->pluck('id');

        $files = SubmissionMediaUpload::query()
            ->whereIn('submission_id', $programmeSubmissionIds)
            ->orWhereIn('site_submission_id', $siteSubmissionIds)
            ->get();

        return $files;
    }

    public static function generateProgrammeSubmissionCsv(Builder $submissionQuery): Object
    {
        $header = [
            'Project ID',
            'Project Name',
            'Project Country',
            'Organization Name',
            'Submission Date of Report',
            'Due Date',
            'Trees Planted Goal',
            'Hecatres Restored Goal',
            'Target Survival Rate of planted trees',
            'Target Crown cover by year 5',
            'Report Author',
            'Techncial Narrative',
            'Public Narrative',
            'Workdays Generated (Paid)',
            'Workdays Generated (Unpaid)',
            '# of Trees Grown in Nurseries',
            '# of Trees Grown in Nuseries by Species + Count',
            'Photos',
            'Additional documents',
        ];

        $records = [];

        $submissionQuery->chunkById(100, function ($submissions) use (&$records) {
            $submissions->each(function (Submission $submission) use (&$records) {
                $programme = $submission->programme;
                $organisation = $programme->organisation;
                $aim = $programme->aim;
                $dueSubmission = $submission->dueSubmission;

                $records[] = [
                    $submission->programme_id,
                    data_get($programme, 'name'),
                    data_get($programme, 'country'),
                    $organisation->approved_version ?
                        $organisation->approved_version->name :
                        $organisation->name,
                    $submission->created_at,
                    data_get($dueSubmission, 'due_at'),
                    data_get($aim, 'year_five_trees'),
                    data_get($aim, 'restoration_hectares'),
                    data_get($aim, 'survival_rate'),
                    data_get($aim, 'year_five_crown_cover'),
                    $submission->created_by,
                    $submission->technical_narrative,
                    $submission->public_narrative,
                    $submission->workdays_paid,
                    $submission->workdays_volunteer,
                    self::handleProgrammeSubmissionTreesSpeciesTotal($submission),
                    self::handleProgrammeSubmissionTreesSpeciesCount($submission),
                    self::handlePhotoFiles($submission, 'programme'),
                    self::handleDocumentFiles($submission, ['programme-submission', 'document_files']),
                ];
            });
        });

        $csv = Writer::createFromString();
        $csv->insertOne($header);
        $csv->insertAll($records);

        return $csv;
    }

    public static function generateSiteSubmissionCsv(Builder $submissionQuery): Object
    {
        $header = [
                'Project ID',
                'Project Name',
                'Project Country',
                'Organization Name',
                'Site ID',
                'Site Name',
                'Site Description',
                'Site History',
                'Planting Pattern',
                'Soil Condition',
                'Restoration Methods ',
                'Submission Date of Report',
                'Due Date',
                'Trees Planted Goal',
                'Hecatres Restored Goal',
                'Target Survival Rate of planted trees',
                'Target Crown cover by year 5',
                'Target Survival Rate of Direct Seeding',
                'Expected total trees per hectares for natural regen',
                'Expected hectares for natural regen',
                'Species to be planted ',
                'Report Author',
                'Techncial Narrative',
                'Workdays Generated (Paid)',
                'Workdays Generated (Unpaid)',
                '# of Planted',
                '# of Trees Planted by Species + Count',
                '# of Seeds planted by species + count',
                '# of Seeds planted by weight and seed mix',
                'Photos',
                'Additional documents',
            ];

        $records = [];

        $submissionQuery->chunkById(100, function ($submissions) use (&$records) {
            $submissions->each(function (SiteSubmission $submission) use (&$records) {
                $site = $submission->site;
                $programme = $site->programme;
                $organisation = $programme->organisation;
                $aim = $programme->aim;

                $records[] = [
                    data_get($programme, 'id'),
                    data_get($programme, 'name'),
                    data_get($programme, 'country'),
                    data_get($organisation, 'name'),
                    data_get($site, 'id'),
                    data_get($site, 'name'),
                    data_get($site, 'description'),
                    data_get($site, 'history'),
                    data_get($site, 'planting_pattern'),
                    data_get($site, 'aim_soil_condition'),
                    implode('|', $site->siteRestorationMethods()->pluck('name')->toArray()),
                    $submission->created_at,
                    $submission->dueSubmission ? $submission->dueSubmission->due_at : null,
                    data_get($site, 'aim_natural_regeneration_trees_per_hectare'),
                    data_get($site, 'aim_natural_regeneration_hectares'),
                    data_get($site, 'aim_survival_rate'),
                    data_get($site, 'aim_year_five_crown_cover'),
                    data_get($site, 'aim_direct_seeding_survival_rate'),
                    data_get($site, 'aim_natural_regeneration_trees_per_hectare'),
                    data_get($site, 'aim_natural_regeneration_hectares'),
                    self::handleSiteTreesSpecies($site),
                    $submission->created_by,
                    $submission->technical_narrative,
                    $submission->workdays_paid,
                    $submission->workdays_volunteer,
                    self::handleSiteTreesSpeciesTotal($submission),
                    self::handleSiteTreesSpeciesCount($submission),
                    self::handleSeedsSpeciesWeight($submission),
                    self::handleSeedsWeight($submission),
                    self::handlePhotoFiles($submission, 'site'),
                    self::handleDocumentFiles($submission, ['site-submission', 'document_files']),
                ];
            });
        });

        $csv = Writer::createFromString();
        $csv->insertOne($header);
        $csv->insertAll($records);

        return $csv;
    }

    public static function handleSiteTreesSpeciesCount(SiteSubmission $submission): string
    {
        $list = [];
        foreach ($submission->siteTreeSpecies as $treeSpecies) {
            $list[] = $treeSpecies->name . ':' . $treeSpecies->amount;
        }

        return implode('|', $list);
    }

    public static function handleSiteTreesSpecies(Site $site): string
    {
        $list = $site->siteTreeSpecies()->pluck('amount')->toArray();

        return implode('|', $list);
    }

    public static function handleProgrammeTreesSpeciesCount(Programme $programme): string
    {
        $list = [];
        foreach ($programme->programmeTreeSpecies as $treeSpecies) {
            $list[] = $treeSpecies->name . ':' . $treeSpecies->amount;
        }

        return implode('|', $list);
    }

    public static function handleProgrammeTreesSpeciesTotal(Programme $programme): int
    {
        return $programme->programmeTreeSpecies()->sum('amount');
    }

    public static function handleProgrammeSubmissionTreesSpeciesCount(Submission $submission): string
    {
        $list = [];
        foreach ($submission->programmeTreeSpecies as $treeSpecies) {
            $list[] = $treeSpecies->name . ':' . $treeSpecies->amount;
        }

        return implode('|', $list);
    }

    public static function handleProgrammeSubmissionTreesSpeciesTotal(Submission $submission): int
    {
        return $submission->programmeTreeSpecies()->sum('amount');
    }

    public static function handleSiteTreesSpeciesTotal(SiteSubmission $siteSubmission): int
    {
        return $siteSubmission->siteTreeSpecies()->sum('amount');
    }

    public static function handleSeedsWeight(SiteSubmission $submission): int
    {
        return $submission->directSeedings()->sum('weight');
    }

    public static function handleSeedsSpeciesWeight(SiteSubmission $submission): string
    {
        $list = [];
        foreach ($submission->directSeedings as $seeding) {
            $list[] = $seeding->weight . ':' . $seeding->name;
        }

        return implode('|', $list);
    }

    public static function handlePhotoFiles($entity, $key): string
    {
        switch ($key) {
            case 'programme':
                $submissionMediaUploads = SubmissionMediaUpload::query()
                ->where('submission_id', $entity->id)
                ->pluck('upload')
                ->toArray();

                break;
            case 'site':
                $submissionMediaUploads = SubmissionMediaUpload::query()
                    ->where('site_submission_id', $entity->id)
                    ->pluck('upload')
                    ->toArray();

                break;
            default:
                $submissionMediaUploads = [];
        }

        return implode(' | ', $submissionMediaUploads);
    }

    public static function handleDocumentFiles($entity, array $collections): string
    {
        $list = [];

        foreach ($entity->getDocumentFileCollection($collections) as $item) {
            $list[] = $item->upload;
        }

        return implode(' | ', $list);
    }
}
