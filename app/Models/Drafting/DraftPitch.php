<?php

namespace App\Models\Drafting;

use App\Models\Upload as UploadModel;

class DraftPitch extends Drafting
{
    public const BLUEPRINT = [
        'pitch' => [
            'name' => null,
            'description' => null,
            'land_types' => [],
            'land_ownerships' => [],
            'land_size' => null,
            'land_continent' => null,
            'land_country' => null,
            'land_geojson' => null,
            'restoration_methods' => [],
            'restoration_goals' => [],
            'funding_sources' => [],
            'funding_amount' => null,
            'funding_bracket' => null,
            'revenue_drivers' => [],
            'estimated_timespan' => null,
            'long_term_engagement' => null,
            'reporting_frequency' => null,
            'reporting_level' => null,
            'sustainable_development_goals' => [],
            'cover_photo' => null,
            'video' => null,
            'problem' => null,
            'anticipated_outcome' => null,
            'who_is_involved' => null,
            'local_community_involvement' => null,
            'training_involved' => null,
            'training_type' => null,
            'training_amount_people' => null,
            'people_working_in' => null,
            'people_amount_nearby' => null,
            'people_amount_abroad' => null,
            'people_amount_employees' => null,
            'people_amount_volunteers' => null,
            'benefited_people' => null,
            'future_maintenance' => null,
            'use_of_resources' => null,
        ],
        'pitch_documents' => [],
        'pitch_contacts' => [],
        'carbon_certifications' => [],
        'restoration_method_metrics' => [],
        'tree_species' => [],
    ];

    public static function transformUploads(Object $data): Object
    {
        if (! is_null($data->pitch->cover_photo)) {
            $data->pitch->cover_photo = UploadModel::findOrFail($data->pitch->cover_photo)->location;
        }
        if (! is_null($data->pitch->video)) {
            $data->pitch->video = UploadModel::findOrFail($data->pitch->video)->location;
        }
        foreach ($data->pitch_documents as &$pitchDocument) {
            if (! is_null($pitchDocument->document)) {
                $pitchDocument->document = UploadModel::findOrFail($pitchDocument->document)->location;
            }
        }

        return $data;
    }

    public static function extractUploads(Object $data): array
    {
        $uploads = [];
        if (! is_null($data->pitch->cover_photo)) {
            $uploads[] = UploadModel::findOrFail($data->pitch->cover_photo);
        }
        if (! is_null($data->pitch->video)) {
            $uploads[] = UploadModel::findOrFail($data->pitch->video);
        }
        foreach ($data->pitch_documents as &$pitchDocument) {
            if (! is_null($pitchDocument->document)) {
                $uploads[] = UploadModel::findOrFail($pitchDocument->document);
            }
        }

        return $uploads;
    }
}
