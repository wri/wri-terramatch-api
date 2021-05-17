<?php

namespace App\Helpers;

use App\Models\Upload as UploadModel;
use Illuminate\Support\Facades\DB;
use Exception;

class DraftHelper
{
    private function __construct()
    {
    }

    public const EMPTY_DATA_OFFER = [
        "offer" => [
            "name" => null,
            "description" => null,
            "land_types" => [],
            "land_ownerships" => [],
            "land_size" => null,
            "land_continent" => null,
            "land_country" => null,
            "restoration_methods" => [],
            "restoration_goals" => [],
            "funding_sources" => [],
            "funding_amount" => null,
            "funding_bracket" => null,
            "price_per_tree" => null,
            "long_term_engagement" => null,
            "reporting_frequency" => null,
            "reporting_level" => null,
            "sustainable_development_goals" => [],
            "cover_photo" => null,
            "video" => null
        ],
        "offer_documents" => [],
        "offer_contacts" => []
    ];

    public const EMPTY_DATA_PITCH = [
        "pitch" => [
            "name" => null,
            "description" => null,
            "land_types" => [],
            "land_ownerships" => [],
            "land_size" => null,
            "land_continent" => null,
            "land_country" => null,
            "land_geojson" => null,
            "restoration_methods" => [],
            "restoration_goals" => [],
            "funding_sources" => [],
            "funding_amount" => null,
            "funding_bracket" => null,
            "revenue_drivers" => [],
            "estimated_timespan" => null,
            "long_term_engagement" => null,
            "reporting_frequency" => null,
            "reporting_level" => null,
            "sustainable_development_goals" => [],
            "cover_photo" => null,
            "video" => null,
            "problem" => null,
            "anticipated_outcome" => null,
            "who_is_involved" => null,
            "local_community_involvement" => null,
            "training_involved" => null,
            "training_type" => null,
            "training_amount_people" => null,
            "people_working_in" => null,
            "people_amount_nearby" => null,
            "people_amount_abroad" => null,
            "people_amount_employees" => null,
            "people_amount_volunteers" => null,
            "benefited_people" => null,
            "future_maintenance" => null,
            "use_of_resources" => null
        ],
        "pitch_documents" => [],
        "pitch_contacts" => [],
        "carbon_certifications" => [],
        "restoration_method_metrics" => [],
        "tree_species" => []
    ];

    public static function transformUploads(String $type, Object $data): Object
    {
        if ($type == "offer") {
            if (!is_null($data->offer->cover_photo)) {
                $data->offer->cover_photo = UploadModel::findOrFail($data->offer->cover_photo)->location;
            }
            if (!is_null($data->offer->video)) {
                $data->offer->video = UploadModel::findOrFail($data->offer->video)->location;
            }
            foreach ($data->offer_documents as &$offerDocument) {
                if (!is_null($offerDocument->document)) {
                    $offerDocument->document = UploadModel::findOrFail($offerDocument->document)->location;
                }
            }
        } else if ($type == "pitch") {
            if (!is_null($data->pitch->cover_photo)) {
                $data->pitch->cover_photo = UploadModel::findOrFail($data->pitch->cover_photo)->location;
            }
            if (!is_null($data->pitch->video)) {
                $data->pitch->video = UploadModel::findOrFail($data->pitch->video)->location;
            }
            foreach ($data->pitch_documents as &$pitchDocument) {
                if (!is_null($pitchDocument->document)) {
                    $pitchDocument->document = UploadModel::findOrFail($pitchDocument->document)->location;
                }
            }
        } else {
            throw new Exception();
        }
        return $data;
    }

    public static function extractUploads(String $type, Object $data): Array
    {
        $uploads = [];
        if ($type == "offer") {
            if (!is_null($data->offer->cover_photo)) {
                $uploads[] = UploadModel::findOrFail($data->offer->cover_photo);
            }
            if (!is_null($data->offer->video)) {
                $uploads[] = UploadModel::findOrFail($data->offer->video);
            }
            foreach ($data->offer_documents as &$offerDocument) {
                if (!is_null($offerDocument->document)) {
                    $uploads[] = UploadModel::findOrFail($offerDocument->document);
                }
            }
        } else if ($type == "pitch") {
            if (!is_null($data->pitch->cover_photo)) {
                $uploads[] = UploadModel::findOrFail($data->pitch->cover_photo);
            }
            if (!is_null($data->pitch->video)) {
                $uploads[] = UploadModel::findOrFail($data->pitch->video);
            }
            foreach ($data->pitch_documents as &$pitchDocument) {
                if (!is_null($pitchDocument->document)) {
                    $uploads[] = UploadModel::findOrFail($pitchDocument->document);
                }
            }
        } else {
            throw new Exception();
        }
        return $uploads;
    }

    public static function findUploadsInDrafts(): Array
    {
        $drafts = DB::select("
            SELECT
            IF(
                `type` = 'offer',
                JSON_MERGE(
                    JSON_ARRAY(
                        JSON_EXTRACT(`data`, '$.offer.cover_photo'),
                        JSON_EXTRACT(`data`, '$.offer.video')
                    ),
                    COALESCE(JSON_EXTRACT(`data`, '$.offer_documents[*].document'), '[]')
                ),
                JSON_MERGE(
                    JSON_ARRAY(
                        JSON_EXTRACT(`data`, '$.pitch.cover_photo'),
                        JSON_EXTRACT(`data`, '$.pitch.video')
                    ),
                    COALESCE(JSON_EXTRACT(`data`, '$.pitch_documents[*].document'), '[]')
                )
            ) AS 'upload_ids'
            FROM `drafts`;
        ");
        $uploadIds = [];
        foreach ($drafts as $draft) {
            if (is_null($draft->upload_ids)) {
                continue;
            }
            foreach (json_decode($draft->upload_ids) as $uploadId) {
                $uploadIds[] = $uploadId;
            }
        }
        return array_unique(array_filter($uploadIds));
    }
}
