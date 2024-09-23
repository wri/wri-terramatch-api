<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("
            ALTER TABLE `drafts`
            CHANGE COLUMN `type` `type`
            ENUM('offer', 'pitch', 'programme', 'site', 'site_submission', 'programme_submission', 'terrafund_programme', 'terrafund_nursery', 'terrafund_site', 'organisation', 'terrafund_nursery_submission', 'terrafund_site_submission') NOT NULL;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::statement("
            ALTER TABLE `drafts`
            CHANGE COLUMN `type` `type`
            ENUM('offer', 'pitch', 'programme', 'site', 'site_submission', 'programme_submission', 'terrafund_programme', 'terrafund_nursery', 'terrafund_site', 'organisation', 'terrafund_nursery_submission') NOT NULL;
        ");
    }
};
