<?php

namespace App\Helpers;

use App\Models\Drafting\DraftingInterface;
use Illuminate\Support\Facades\DB;

class DraftHelper
{
    public static function drafting(string $type): DraftingInterface
    {
        return app('App\Models\Drafting\Draft' . str_replace('_', '', ucwords($type,  '_')));
    }

    public static function findUploadsInDrafts(): array
    {
        $drafts = DB::select("
            SELECT
            IF(`type` = 'offer',
                JSON_MERGE(
                    JSON_ARRAY(
                        JSON_EXTRACT(`data`, '$.offer.cover_photo'),
                        JSON_EXTRACT(`data`, '$.offer.video')
                    ),
                    COALESCE(JSON_EXTRACT(`data`, '$.offer_documents[*].document'), '[]')
                ),
                IF(`type` = 'pitch',
                    JSON_MERGE(
                        JSON_ARRAY(
                            JSON_EXTRACT(`data`, '$.pitch.cover_photo'),
                            JSON_EXTRACT(`data`, '$.pitch.video')
                        ),
                        COALESCE(JSON_EXTRACT(`data`, '$.pitch_documents[*].document'), '[]')
                    ),
                    IF(`type` = 'programme',
                        JSON_MERGE(
                            JSON_ARRAY(
                                JSON_EXTRACT(`data`, '$.programme_tree_species_file'),
                                JSON_EXTRACT(`data`, '$.programme.thumbnail')
                            ),
                            COALESCE(JSON_EXTRACT(`data`, '$.document_files[*].upload'), '[]')
                        ),
                        IF(`type` = 'site',
                            JSON_MERGE(
                                JSON_ARRAY(
                                    JSON_EXTRACT(`data`, '$.socioeconomic_benefits'),
                                    JSON_EXTRACT(`data`, '$.site.stratification_for_heterogeneity'),
                                    JSON_EXTRACT(`data`, '$.site_tree_species_file')
                                ),
                                COALESCE(JSON_EXTRACT(`data`, '$.media[*].upload'), '[]'),
                                COALESCE(JSON_EXTRACT(`data`, '$.document_files[*].upload'), '[]')
                            ),
                            IF(`type` = 'site_submission',
                                JSON_MERGE(
                                    JSON_ARRAY(
                                        JSON_EXTRACT(`data`, '$.socioeconomic_benefits'),
                                        JSON_EXTRACT(`data`, '$.site_tree_species_file')
                                    ),
                                    COALESCE(JSON_EXTRACT(`data`, '$.media[*].upload'), '[]'),
                                    COALESCE(JSON_EXTRACT(`data`, '$.document_files[*].upload'), '[]')
                                ),
                                IF(`type` = 'programme_submission',
                                    JSON_MERGE(
                                        JSON_ARRAY(
                                            JSON_EXTRACT(`data`, '$.socioeconomic_benefits'),
                                            JSON_EXTRACT(`data`, '$.programme_tree_species_file')
                                        ),
                                        COALESCE(JSON_EXTRACT(`data`, '$.media[*].upload'), '[]'),
                                        COALESCE(JSON_EXTRACT(`data`, '$.document_files[*].upload'), '[]')
                                    ),
                                    IF(`type` = 'terrafund_programme',
                                        JSON_MERGE(
                                            JSON_ARRAY(
                                                JSON_EXTRACT(`data`, '$.tree_species_csv')
                                            ),
                                            COALESCE(JSON_EXTRACT(`data`, '$.additional_files[*].upload'), '[]')
                                        ),
                                        IF(`type` = 'terrafund_nursery',
                                            JSON_MERGE(
                                                JSON_ARRAY(
                                                    JSON_EXTRACT(`data`, '$.tree_species_csv')
                                                ),
                                                COALESCE(JSON_EXTRACT(`data`, '$.photos[*].upload'), '[]')
                                            ),
                                            IF(`type` = 'organisation',
                                                JSON_MERGE(
                                                    JSON_ARRAY(
                                                        JSON_EXTRACT(`data`, '$.organisation.cover_photo'),
                                                        JSON_EXTRACT(`data`, '$.organisation.avatar')
                                                    ),
                                                    COALESCE(JSON_EXTRACT(`data`, '$.photos[*].upload'), '[]'),
                                                    COALESCE(JSON_EXTRACT(`data`, '$.files[*].upload'), '[]')
                                                ),
                                                IF(`type` = 'terrafund_site',
                                                    COALESCE(JSON_EXTRACT(`data`, '$.photos[*].upload'), '[]'),
                                                    IF(`type` = 'terrafund_nursery_submission',
                                                        COALESCE(JSON_EXTRACT(`data`, '$.photos[*].upload'), '[]'),
                                                        IF(`type` = 'terrafund_site_submission',
                                                            JSON_MERGE(
                                                                JSON_ARRAY(
                                                                    JSON_EXTRACT(`data`, '$.tree_species_csv')
                                                                ),
                                                                COALESCE(JSON_EXTRACT(`data`, '$.photos[*].upload'), '[]')
                                                            ),
                                                            IF(`type` = 'terrafund_programme_submission',
                                                                COALESCE(JSON_EXTRACT(`data`, '$.photos[*].upload'), '[]'),
                                                                '[]'
                                                            )
                                                        )
                                                    )
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            ) AS 'uploads'
            FROM `drafts`;
        ");
        $uploadIds = [];
        foreach ($drafts as $draft) {
            if (is_null($draft->uploads)) {
                continue;
            }
            foreach (json_decode($draft->uploads) as $uploadId) {
                $uploadIds[] = $uploadId;
            }
        }

        return array_unique(array_filter($uploadIds));
    }
}
