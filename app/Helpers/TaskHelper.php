<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class TaskHelper
{
    private function __construct()
    {
    }

    public static function findPendingPitches(): array
    {
        return DB::select("
            SELECT pitch_id, MAX(created_at) AS max_created_at FROM (
                SELECT pitch_id, created_at 
                FROM pitch_versions 
                WHERE status = 'pending'
                UNION
                SELECT carbon_certifications.pitch_id, carbon_certification_versions.created_at
                FROM carbon_certification_versions 
                LEFT JOIN carbon_certifications ON carbon_certification_versions.carbon_certification_id = carbon_certifications.id
                WHERE carbon_certification_versions.status = 'pending' 
                AND carbon_certifications.deleted_at IS NULL
                UNION
                SELECT pitch_documents.pitch_id, pitch_document_versions.created_at
                FROM pitch_document_versions 
                LEFT JOIN pitch_documents ON pitch_document_versions.pitch_document_id = pitch_documents.id
                WHERE pitch_document_versions.status = 'pending' 
                AND pitch_documents.deleted_at IS NULL
                UNION
                SELECT restoration_method_metrics.pitch_id, restoration_method_metric_versions.created_at
                FROM restoration_method_metric_versions 
                LEFT JOIN restoration_method_metrics ON restoration_method_metric_versions.restoration_method_metric_id = restoration_method_metrics.id
                WHERE restoration_method_metric_versions.status = 'pending' 
                AND restoration_method_metrics.deleted_at IS NULL
                UNION
                SELECT tree_species.pitch_id, tree_species_versions.created_at
                FROM tree_species_versions 
                LEFT JOIN tree_species ON tree_species_versions.tree_species_id = tree_species.id
                WHERE tree_species_versions.status = 'pending' 
                AND tree_species.deleted_at IS NULL
            ) AS tasks
            GROUP BY pitch_id ORDER BY max_created_at DESC;
        ");
    }

    public static function findPendingOrganisations(): array
    {
        return DB::select("
            SELECT organisation_id, MAX(created_at) AS max_created_at FROM (
                SELECT organisation_id, created_at 
                FROM organisation_versions 
                WHERE status = 'pending'
                UNION
                SELECT organisation_documents.organisation_id, organisation_document_versions.created_at
                FROM organisation_document_versions 
                LEFT JOIN organisation_documents ON organisation_document_versions.organisation_document_id = organisation_documents.id
                WHERE organisation_document_versions.status = 'pending' 
                AND organisation_documents.deleted_at IS NULL
            ) AS tasks
            GROUP BY organisation_id ORDER BY max_created_at DESC;
        ");
    }
}
