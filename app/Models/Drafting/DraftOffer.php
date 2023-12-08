<?php

namespace App\Models\Drafting;

use App\Models\Upload as UploadModel;

class DraftOffer extends Drafting
{
    public const BLUEPRINT = [
        'offer' => [
            'name' => null,
            'description' => null,
            'land_types' => [],
            'land_ownerships' => [],
            'land_size' => null,
            'land_continent' => null,
            'land_country' => null,
            'restoration_methods' => [],
            'restoration_goals' => [],
            'funding_sources' => [],
            'funding_amount' => null,
            'funding_bracket' => null,
            'price_per_tree' => null,
            'long_term_engagement' => null,
            'reporting_frequency' => null,
            'reporting_level' => null,
            'sustainable_development_goals' => [],
            'cover_photo' => null,
            'video' => null,
        ],
        'offer_documents' => [],
        'offer_contacts' => [],
    ];

    public static function transformUploads(Object $data): Object
    {
        if (! is_null($data->offer->cover_photo)) {
            $data->offer->cover_photo = UploadModel::findOrFail($data->offer->cover_photo)->location;
        }
        if (! is_null($data->offer->video)) {
            $data->offer->video = UploadModel::findOrFail($data->offer->video)->location;
        }
        foreach ($data->offer_documents as &$offerDocument) {
            if (! is_null($offerDocument->document)) {
                $offerDocument->document = UploadModel::findOrFail($offerDocument->document)->location;
            }
        }

        return $data;
    }

    public static function extractUploads(Object $data): array
    {
        $uploads = [];
        if (! is_null($data->offer->cover_photo)) {
            $uploads[] = UploadModel::findOrFail($data->offer->cover_photo);
        }
        if (! is_null($data->offer->video)) {
            $uploads[] = UploadModel::findOrFail($data->offer->video);
        }
        foreach ($data->offer_documents as &$offerDocument) {
            if (! is_null($offerDocument->document)) {
                $uploads[] = UploadModel::findOrFail($offerDocument->document);
            }
        }

        return $uploads;
    }
}
