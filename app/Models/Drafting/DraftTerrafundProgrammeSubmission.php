<?php

namespace App\Models\Drafting;

use App\Models\Upload as UploadModel;

class DraftTerrafundProgrammeSubmission extends Drafting
{
    public const BLUEPRINT = [
        'terrafund_programme_submission' => [
            'shared_drive_link' => null,
            'landscape_community_contribution' => null,
            'top_three_successes' => null,
            'maintenance_and_monitoring_activities' => null,
            'significant_change' => null,
            'percentage_survival_to_date' => null,
            'survival_calculation' => null,
            'survival_comparison' => null,
            'ft_women' => null,
            'ft_men' => null,
            'ft_youth' => null,
            'ft_total' => null,
            'pt_women' => null,
            'pt_men' => null,
            'pt_youth' => null,
            'pt_total' => null,
            'volunteer_women' => null,
            'volunteer_men' => null,
            'volunteer_youth' => null,
            'volunteer_total' => null,
            'people_annual_income_increased' => null,
            'people_knowledge_skills_increased' => null,
            'challenges_faced' => null,
            'challenges_and_lessons' => null,
            'lessons_learned' => null,
            'planted_trees' => null,
            'new_jobs_created' => null,
            'new_jobs_description' => null,
            'new_volunteers' => null,
            'volunteers_work_description' => null,
            'full_time_jobs_35plus' => null,
            'part_time_jobs_35plus' => null,
            'volunteer_35plus' => null,
            'beneficiaries' => null,
            'beneficiaries_description' => null,
            'women_beneficiaries' => null,
            'men_beneficiaries' => null,
            'beneficiaries_35plus' => null,
            'youth_beneficiaries' => null,
            'smallholder_beneficiaries' => null,
            'large_scale_beneficiaries' => null,
            'beneficiaries_income_increase' => null,
            'income_increase_description' => null,
            'beneficiaries_skills_knowledge_increase' => null,
            'skills_knowledge_description' => null,
        ],
        'photos' => [],
        'other_additional_documents' => [],
        'survival_rate_skipped' => null,
        'jobs_skipped' => null,
        'volunteers_skipped' => null,
        'beneficiaries_skipped' => null,
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

        if (property_exists($data, 'other_additional_documents') && ! is_null($data->other_additional_documents)) {
            foreach ($data->other_additional_documents as &$document) {
                if (! is_null($document)) {
                    $uploadModel = UploadModel::findOrFail($document->upload);
                    $media = [
                        'id' => $uploadModel->id,
                        'is_public' => $document->is_public,
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

        if (! is_null($data->other_additional_documents) && count($data->other_additional_documents) > 0) {
            foreach ($data->other_additional_documents as &$document) {
                if (! is_null($document)) {
                    $uploads[] = UploadModel::findOrFail($document->upload);
                }
            }
        }

        return $uploads;
    }
}
