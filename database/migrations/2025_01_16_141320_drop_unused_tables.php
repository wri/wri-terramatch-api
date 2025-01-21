<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected array $tablesToDrop = [
      // 'aims',
      // 'baseline_monitoring_metrics_project',
      // 'baseline_monitoring_metrics_site',
      // 'carbon_certification_versions',
      // 'carbon_certifications',
      // 'csv_imports',
      // 'devices',
      'direct_seedings', // AQUI
      // 'document_files',
      // 'drafts',
      // 'due_submissions',
      // 'edit_histories',
      // 'elevator_videos',
      // 'filter_records',
      'form_common_options', //AQui
      'form_common_options_questions', //Aqui
      // 'framework_invite_codes',
      // 'i18n_translations_bak',
      // 'interests',
      // 'invasives',
      // 'land_tenure_site',
      // 'land_tenures',
      // 'matches',
      // 'media_uploads',
      // 'monitorings',
      // 'notifications',
      // 'notifications_buffer',
      // 'offer_contacts',
      // 'offer_documents',
      // 'offers',
      // 'organisation_document_versions',
      // 'organisation_documents',
      // 'organisation_files',
      // 'organisation_photos',
      // 'organisation_user',
      // 'organisation_versions',
      // 'pitch_contacts',
      // 'pitch_document_versions',
      // 'pitch_documents',
      // 'pitch_versions',
      // 'pitches',
      // 'programme_invites',
      // 'programme_tree_species',
      // 'programme_user',
      // 'programmes',
      // 'progress_updates',
      // 'restoration_method_metric_versions',
      // 'restoration_method_metrics',
      // 'satellite_maps',
      // 'satellite_monitors',
      // 'saved_exports',
      'seed_details', //aqui
      // 'shapefiles',
      // 'site_csv_imports',
      // 'site_restoration_method_site',
      // 'site_restoration_methods',
      // 'site_submission_disturbances',
      // 'site_submissions',
      // 'site_tree_species',
      // 'sites',
      // 'socioeconomic_benefits',
      // 'submission_media_uploads',
      // 'submissions',
      // 'targets',
      // 'team_members',
      // 'terrafund_csv_imports',
      // 'terrafund_disturbances',
      // 'terrafund_due_submissions',
      // 'terrafund_files',
      // 'terrafund_none_tree_species',
      // 'terrafund_nurseries',
      // 'terrafund_nursery_submissions',
      // 'terrafund_programme_invites',
      // 'terrafund_programme_submissions',
      // 'terrafund_programme_user',
      'terrafund_programmes', // aqui
      // 'terrafund_site_submissions',
      // 'terrafund_sites',
      // 'terrafund_tree_species',
      // 'tree_species',
      // 'tree_species_versions',
      // 'uploads',
      // 'v2_temporary_sites',
];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($this->tablesToDrop as $table) {
                if (Schema::hasTable($table)) {
                    // Get all foreign key constraints pointing to this table
                    $constraints = $this->getForeignKeyConstraints($table);

                    // Drop each constraint
                    foreach ($constraints as $constraint) {
                        Schema::table($constraint->TABLE_NAME, function (Blueprint $table) use ($constraint) {
                            $table->dropForeign($constraint->CONSTRAINT_NAME);
                        });
                    }

                    // Drop the table itself
                    Schema::dropIfExists($table);
                }
            }
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    private function getForeignKeyConstraints(string $tableName): array
    {
        return DB::select('
            SELECT 
                TABLE_NAME,
                CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = ?
            AND REFERENCED_TABLE_NAME = ?
        ', [env('DB_DATABASE'), $tableName]);
    }
};
