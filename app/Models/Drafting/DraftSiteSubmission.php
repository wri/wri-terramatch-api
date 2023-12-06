<?php

namespace App\Models\Drafting;

use App\Models\Upload as UploadModel;

class DraftSiteSubmission extends Drafting
{
    public const BLUEPRINT = [
        'site_submission' => [
            'site_id' => null,
            'created_by' => null,
        ],
        'narratives' => [
            'technical_narrative' => null,
            'public_narrative' => null,
        ],
        'site_tree_species' => [],
        'site_tree_species_file' => null,
        'direct_seeding' => [
            'direct_seeding_kg' => null,
            'kg_by_species' => [],
        ],
        'disturbance_information' => null,
        'disturbances' => [],
        'socioeconomic_benefits' => null,
        'workdays_paid' => null,
        'workdays_volunteer' => null,
        'media' => [],
        'document_files' => [],
        'additional_tree_species' => null,
        'progress' => [
            'jobs_and_livelihoods_skipped' => null,
            'direct_seeding_skipped' => null,
            'trees_planted_skipped' => null,
            'disturbances_skipped' => null,
        ],
    ];

    public static function transformUploads(Object $data): Object
    {
        if (! is_null($data->socioeconomic_benefits)) {
            $data->socioeconomic_benefits = UploadModel::findOrFail($data->socioeconomic_benefits)->location;
        }
        if (! is_null($data->site_tree_species_file)) {
            $data->site_tree_species_file = UploadModel::findOrFail($data->site_tree_species_file)->location;
        }
        if (! is_null($data->media) && count($data->media) > 0) {
            foreach ($data->media as &$media) {
                if (! is_null($media)) {
                    $uploadModel = UploadModel::findOrFail($media->upload);
                    $media = [
                        'id' => $uploadModel->id,
                        'is_public' => $media->is_public,
                        'upload' => $uploadModel->location,
                    ];
                }
            }
        }
        if (! empty($data->document_files) && count($data->document_files) > 0) {
            foreach ($data->document_files as &$document_file) {
                if (! is_null($document_file)) {
                    $uploadModel = UploadModel::findOrFail($document_file->upload);
                    $document_file = [
                        'id' => $uploadModel->id,
                        'is_public' => data_get($document_file, 'is_public', false),
                        'title' => data_get($document_file, 'title', ''),
                        'collection' => data_get($document_file, 'collection', 'general'),
                        'upload' => data_get($uploadModel, 'location', ''),
                    ];
                }
            }
        }

        return $data;
    }

    public static function extractUploads(Object $data): array
    {
        $uploads = [];
        if (! is_null($data->socioeconomic_benefits)) {
            $uploads[] = UploadModel::findOrFail($data->socioeconomic_benefits);
        }
        if (! is_null($data->site_tree_species_file)) {
            $uploads[] = UploadModel::findOrFail($data->site_tree_species_file);
        }
        if (! is_null($data->media) && count($data->media) > 0) {
            foreach ($data->media as &$media) {
                if (! is_null($media)) {
                    $uploads[] = UploadModel::findOrFail($media->upload);
                }
            }
        }
        if (! empty($data->document_files) && count($data->document_files) > 0) {
            foreach ($data->document_files as &$document_file) {
                if (! is_null($document_file)) {
                    $uploads[] = UploadModel::findOrFail($document_file->upload);
                }
            }
        }

        return $uploads;
    }
}
