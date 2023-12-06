<?php

namespace App\Models\Drafting;

use App\Models\Upload as UploadModel;

class DraftProgrammeSubmission extends Drafting
{
    public const BLUEPRINT = [
        'programme_submission' => [
            'programme_id' => null,
            'title' => null,
            'created_by' => null,
        ],
        'narratives' => [
            'technical_narrative' => null,
            'public_narrative' => null,
        ],
        'programme_tree_species' => [],
        'programme_tree_species_file' => null,
        'socioeconomic_benefits' => null,
        'workdays_paid' => null,
        'workdays_volunteer' => null,
        'media' => [],
        'document_files' => [],
        'additional_tree_species' => null,
        'progress' => [
            'jobs_and_livelihoods_skipped' => null,
            'trees_planted_skipped' => null,
        ],
    ];

    public static function transformUploads(Object $data): Object
    {
        if (! is_null($data->programme_tree_species_file)) {
            $data->programme_tree_species_file = UploadModel::findOrFail($data->programme_tree_species_file)->location;
        }
        if (! is_null($data->socioeconomic_benefits)) {
            $data->socioeconomic_benefits = UploadModel::findOrFail($data->socioeconomic_benefits)->location;
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
        if (! is_null($data->programme_tree_species_file)) {
            $uploads[] = UploadModel::findOrFail($data->programme_tree_species_file);
        }
        if (! is_null($data->socioeconomic_benefits)) {
            $uploads[] = UploadModel::findOrFail($data->socioeconomic_benefits);
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
