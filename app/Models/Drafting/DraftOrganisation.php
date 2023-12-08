<?php

namespace App\Models\Drafting;

use App\Models\Upload as UploadModel;

class DraftOrganisation extends Drafting
{
    public const BLUEPRINT = [
        'organisation' => [
            'name' => null,
            'description' => null,
            'address_1' => null,
            'address_2' => null,
            'city' => null,
            'state' => null,
            'zip_code' => null,
            'country' => null,
            'phone_number' => null,
            'full_time_permanent_employees' => null,
            'seasonal_employees' => null,
            'part_time_permanent_employees' => null,
            'percentage_female' => null,
            'percentage_youth' => null,
            'website' => null,
            'key_contact' => null,
            'type' => null,
            'account_type' => null,
            'category' => null,
            'facebook' => null,
            'twitter' => null,
            'linkedin' => null,
            'instagram' => null,
            'avatar' => null,
            'cover_photo' => null,
            'video' => null,
            'revenues_19' => null,
            'revenues_20' => null,
            'revenues_21' => null,
            'founded_at' => null,
            'community_engagement_strategy' => null,
            'three_year_community_engagement' => null,
            'women_farmer_engagement' => null,
            'young_people_engagement' => null,
            'monitoring_and_evaluation_experience' => null,
            'community_follow_up' => null,
            'total_hectares_restored' => null,
            'hectares_restored_three_years' => null,
            'total_trees_grown' => null,
            'tree_survival_rate' => null,
            'tree_maintenance_and_aftercare' => null,
        ],
        'photos' => [],
        'files' => [],
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
        if (! is_null($data->files) && count($data->files) > 0) {
            foreach ($data->files as &$file) {
                if (! is_null($file)) {
                    $uploadModel = UploadModel::findOrFail($file->upload);
                    $media = [
                        'id' => $uploadModel->id,
                        'type' => $file->type,
                        'upload' => $uploadModel->location,
                    ];
                }
            }
        }
        if (! is_null($data->organisation->cover_photo)) {
            $uploadModel = UploadModel::findOrFail($data->organisation->cover_photo);
            $data->organisation->cover_photo = [
                'id' => $uploadModel->id,
                'upload' => $uploadModel->location,
            ];
        }
        if (! is_null($data->organisation->avatar)) {
            $uploadModel = UploadModel::findOrFail($data->organisation->avatar);
            $data->organisation->avatar = [
                'id' => $uploadModel->id,
                'upload' => $uploadModel->location,
            ];
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
        if (! is_null($data->files) && count($data->files) > 0) {
            foreach ($data->files as &$file) {
                if (! is_null($file)) {
                    $uploads[] = UploadModel::findOrFail($file->upload);
                }
            }
        }
        if (! is_null($data->organisation->cover_photo)) {
            $uploads[] = UploadModel::findOrFail($data->organisation->cover_photo);
        }
        if (! is_null($data->organisation->avatar)) {
            $uploads[] = UploadModel::findOrFail($data->organisation->avatar);
        }

        return $uploads;
    }
}
