<?php

namespace App\Models\Drafting;

use App\Models\Upload as UploadModel;

class DraftSite extends Drafting
{
    public const BLUEPRINT = [
        'site' => [
            'programme_id' => null,
            'site_name' => null,
            'site_description' => null,
            'site_history' => null,
            'end_date' => null,
            'planting_pattern' => null,
            'stratification_for_heterogeneity' => null,
            'control_site' => false,
        ],
        'boundary' => [
            'boundary_geojson' => null,
        ],
        'narratives' => [
            'technical_narrative' => null,
            'public_narrative' => null,
        ],
        'aims' => [
            'aim_survival_rate' => null,
            'aim_year_five_crown_cover' => null,
            'aim_direct_seeding_survival_rate' => null,
            'aim_natural_regeneration_trees_per_hectare' => null,
            'aim_natural_regeneration_hectares' => null,
            'aim_soil_condition' => null,
            'aim_number_of_mature_trees' => null,
        ],
        'establishment_date' => [
            'establishment_date' => null,
        ],
        'restoration_methods' => [
            'site_restoration_method_ids' => [],
        ],
        'socioeconomic_benefits' => null,
        'seeds' => [],
        'invasives' => [],
        'media' => [],
        'document_files' => [],
        'land_tenure' => [],
        'site_tree_species' => [],
        'site_tree_species_file' => null,
        'additional_tree_species' => null,
        'progress' => [
            'invasives_skipped' => null,
        ],
    ];

    public static function transformUploads(Object $data): Object
    {
        if (! is_null($data->socioeconomic_benefits)) {
            $data->socioeconomic_benefits = UploadModel::findOrFail($data->socioeconomic_benefits)->location;
        }
        if (! is_null($data->site->stratification_for_heterogeneity)) {
            $data->site->stratification_for_heterogeneity = UploadModel::findOrFail($data->site->stratification_for_heterogeneity)->location;
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
        if (! is_null($data->site->stratification_for_heterogeneity)) {
            $uploads[] = UploadModel::findOrFail($data->site->stratification_for_heterogeneity);
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
