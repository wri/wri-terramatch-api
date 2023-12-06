<?php

namespace App\Models\Drafting;

use App\Models\Upload as UploadModel;
use Illuminate\Support\Facades\Log;

class DraftProgramme extends Drafting
{
    public const BLUEPRINT = [
        'programme' => [
            'name' => null,
            'continent' => null,
            'country' => null,
            'end_date' => null,
            'thumbnail' => null,
        ],
        'boundary' => [
            'boundary_geojson' => null,
        ],
        'programme_tree_species' => [],
        'programme_tree_species_file' => null,
        'additional_tree_species' => null,
        'document_files' => [],
        'aims' => [
            'year_five_trees' => null,
            'restoration_hectares' => null,
            'survival_rate' => null,
            'year_five_crown_cover' => null,
        ],
    ];

    public static function transformUploads(Object $data): Object
    {
        if (! empty($data->programme_tree_species_file)) {
            $data->programme_tree_species_file = UploadModel::findOrFail($data->programme_tree_species_file)->location;
        }
        if (! empty($data->programme->thumbnail)) {
            if (gettype($data->programme->thumbnail) !== 'integer') {
                Log::warning('Draft Programme Thumbnail - ID not passed :', [$data->programme->thumbnail]);
            } else {
                $data->programme->thumbnail = UploadModel::findOrFail($data->programme->thumbnail)->location;
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
        if (! empty($data->programme_tree_species_file) && gettype($data->programme_tree_species_file) === 'integer') {
            $uploads[] = UploadModel::findOrFail($data->programme_tree_species_file);
        }
        if (! empty($data->programme->thumbnail) && gettype($data->programme->thumbnail) === 'integer') {
            $uploads[] = UploadModel::findOrFail($data->programme->thumbnail);
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
