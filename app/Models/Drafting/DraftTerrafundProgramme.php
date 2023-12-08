<?php

namespace App\Models\Drafting;

use App\Models\Upload as UploadModel;

class DraftTerrafundProgramme extends Drafting
{
    public const BLUEPRINT = [
        'terrafund_programme' => [
            'name' => null,
            'description' => null,
            'planting_start_date' => null,
            'planting_end_date' => null,
            'budget' => null,
            'status' => null,
            'project_country' => null,
            'home_country' => null,
            'boundary_geojson' => null,
            'history' => null,
            'objectives' => null,
            'environmental_goals' => null,
            'socioeconomic_goals' => null,
            'sdgs_impacted' => null,
            'long_term_growth' => null,
            'community_incentives' => null,
            'total_hectares_restored' => null,
            'trees_planted' => null,
            'jobs_created' => null,
        ],
        'tree_species' => [],
        'tree_species_csv' => null,
        'additional_files' => [],
    ];

    public static function transformUploads(Object $data): Object
    {
        if (! is_null($data->tree_species_csv)) {
            $data->tree_species_csv = UploadModel::findOrFail($data->tree_species_csv)->location;
        }
        if (! is_null($data->additional_files) && count($data->additional_files) > 0) {
            foreach ($data->additional_files as &$file) {
                if (! is_null($file)) {
                    $uploadModel = UploadModel::findOrFail($file->upload);
                    $media = [
                        'id' => $uploadModel->id,
                        'is_public' => $file->is_public,
                        'upload' => $uploadModel->location,
                    ];
                }
            }
        }

        return $data;
    }

    public static function extractUploads(Object $data): array
    {
        $uploads = [];
        if (! is_null($data->tree_species_csv)) {
            $uploads[] = UploadModel::findOrFail($data->tree_species_csv);
        }
        if (! is_null($data->additional_files) && count($data->additional_files) > 0) {
            foreach ($data->additional_files as &$file) {
                if (! is_null($file)) {
                    $uploads[] = UploadModel::findOrFail($file->upload);
                }
            }
        }

        return $uploads;
    }
}
