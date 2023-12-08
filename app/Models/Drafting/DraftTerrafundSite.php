<?php

namespace App\Models\Drafting;

use App\Models\Upload as UploadModel;

class DraftTerrafundSite extends Drafting
{
    public const BLUEPRINT = [
        'terrafund_site' => [
            'name' => null,
            'start_date' => null,
            'end_date' => null,
            'boundary_geojson' => null,
            'terrafund_programme_id' => null,
            'restoration_methods' => [],
            'land_tenures' => [],
            'hectares_to_restore' => null,
            'landscape_community_contribution' => null,
            'disturbances' => null,
        ],
        'photos' => [],
    ];

    public static function transformUploads(Object $data): Object
    {
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
