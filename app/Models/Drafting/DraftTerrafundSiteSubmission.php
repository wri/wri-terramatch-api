<?php

namespace App\Models\Drafting;

use App\Models\Upload as UploadModel;

class DraftTerrafundSiteSubmission extends Drafting
{
    public const BLUEPRINT = [
        'terrafund_site_submission' => [
            'terrafund_site_id' => null,
            'shared_drive_link' => null,
        ],
        'photos' => [],
        'tree_species' => [],
        'tree_species_csv' => null,
        'non_tree_species' => [],
        'disturbances' => [],
    ];

    public static function transformUploads(Object $data): Object
    {
        if (! is_null($data->tree_species_csv)) {
            $data->tree_species_csv = UploadModel::findOrFail($data->tree_species_csv)->location;
        }
        if (! is_null($data->photos) && count($data->photos) > 0) {
            foreach ($data->photos as &$photo) {
                if (! is_null($photo)) {
                    $uploadModel = UploadModel::findOrFail($photo->upload);
                    $media = [
                        'id' => $uploadModel->id,
                        'is_public' => $photo->is_public,
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
        if (! empty($data->tree_species_csv)) {
            $uploads[] = UploadModel::findOrFail($data->tree_species_csv);
        }
        if (! is_null($data->photos) && count($data->photos) > 0) {
            foreach ($data->photos as &$photo) {
                if (! is_null($photo)) {
                    $uploads[] = UploadModel::findOrFail($photo->upload);
                }
            }
        }

        return $uploads;
    }
}
