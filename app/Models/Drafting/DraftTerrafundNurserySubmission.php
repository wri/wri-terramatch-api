<?php

namespace App\Models\Drafting;

use App\Models\Upload as UploadModel;

class DraftTerrafundNurserySubmission extends Drafting
{
    public const BLUEPRINT = [
        'terrafund_nursery_submission' => [
            'seedlings_young_trees' => null,
            'interesting_facts' => null,
            'site_prep' => null,
            'shared_drive_link' => null,
            'terrafund_nursery_id' => null,
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
