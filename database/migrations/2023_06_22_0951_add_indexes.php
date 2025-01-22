<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->unique('uuid');
            $table->index('funding_programme_uuid');
            $table->index('organisation_uuid');
            $table->index(['deleted_at','funding_programme_uuid'], 'applications_idx_deleted_at_funding_uuid');
        });

        Schema::table('document_files', function (Blueprint $table) {
            $table->unique('uuid');
            $table->index(['document_fileable_type', 'document_fileable_id'], 'documentfileable');
            $table->index(['document_fileable_type', 'document_fileable_id', 'collection'], 'documentfileable_collection');
        });

        Schema::table('edit_histories', function (Blueprint $table) {
            $table->index(['projectable_type', 'projectable_id']);
            $table->index(['editable_type', 'editable_id']);
            $table->index('organisation_id');
            $table->index(['editable_type', 'editable_id', 'status']);
            $table->index('status');
        });

        Schema::table('form_question_options', function (Blueprint $table) {
            $table->index('slug');
        });

        Schema::table('form_sections', function (Blueprint $table) {
            $table->index('form_id');
        });

        Schema::table('form_submissions', function (Blueprint $table) {
            $table->index('status');
            $table->index('stage_uuid');
            $table->index('application_id');
        });

        Schema::table('form_table_headers', function (Blueprint $table) {
            $table->unique('uuid');
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->index('stage_id');
            $table->index(['stage_id', 'version']);
        });

        Schema::table('i18n_items', function (Blueprint $table) {
            $table->index('status');
            $table->index('hash');
        });

        Schema::table('i18n_translations', function (Blueprint $table) {
            $table->index('i18n_item_id');
            $table->index(['i18n_item_id', 'language']);
        });

        Schema::table('project_pitches', function (Blueprint $table) {
            $table->index('organisation_id');
            $table->index('funding_programme_id');
            $table->index('status');
        });

        Schema::table('site_submissions', function (Blueprint $table) {
            $table->index('due_submission_id');
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->index(['programme_id', 'control_site']);
        });

        Schema::table('stages', function (Blueprint $table) {
            $table->index('funding_programme_id');
            $table->index(['funding_programme_id', 'status']);
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->index('due_submission_id');
        });

        Schema::table('terrafund_nursery_submissions', function (Blueprint $table) {
            $table->index('terrafund_due_submission_id');
            $table->index('terrafund_nursery_id');
        });
        Schema::table('terrafund_site_submissions', function (Blueprint $table) {
            $table->index('terrafund_due_submission_id');
            $table->index('terrafund_site_id');
        });

        Schema::table('v2_funding_types', function (Blueprint $table) {
            $table->unique('uuid');
            $table->index('organisation_id');
        });

        Schema::table('v2_leadership_team', function (Blueprint $table) {
            $table->unique('uuid');
            $table->index('organisation_id');
        });

        Schema::table('v2_temporary_sites', function (Blueprint $table) {
            $table->unique('uuid');
            $table->index('site_id');
            $table->index('terrafund_site_id');
        });

        Schema::table('organisations', function (Blueprint $table) {
            $table->index(['deleted_at','uuid'], 'organisations_idx_deleted_at_uuid');
        });

        Schema::table('organisation_files', function (Blueprint $table) {
            $table->index('organisation_id');
        });

        Schema::table('organisation_photos', function (Blueprint $table) {
            $table->index('organisation_id');
        });

        Schema::table('organisation_versions', function (Blueprint $table) {
            $table->index('status');
            $table->index(['organisation_id', 'status']);
        });
    }
}
