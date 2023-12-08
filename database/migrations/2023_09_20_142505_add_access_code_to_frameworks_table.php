<?php

use App\Models\Framework;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddAccessCodeToFrameworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('frameworks', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->index()->after('id');
            $table->string('access_code')->nullable()->after('slug');
            $table->uuid('project_form_uuid')->nullable()->after('access_code');
            $table->uuid('project_report_form_uuid')->nullable()->after('project_form_uuid');
            $table->uuid('site_form_uuid')->nullable()->after('project_report_form_uuid');
            $table->uuid('site_report_form_uuid')->nullable()->after('site_form_uuid');
            $table->uuid('nursery_form_uuid')->nullable()->after('site_report_form_uuid');
            $table->uuid('nursery_report_form_uuid')->nullable()->after('nursery_form_uuid');
        });

        $frameworks = Framework::all();
        foreach ($frameworks as $framework) {
            if (empty($framework->uuid)) {
                $framework->uuid = Str::uuid();
            }

            $framework->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('frameworks', function (Blueprint $table) {
            $table->dropColumn([
                'access_code',
                'project_form_uuid',
                'project_report_form_uuid',
                'site_form_uuid',
                'site_report_form_uuid',
                'nursery_form_uuid',
                'nursery_report_form_uuid',
            ]);
        });
    }
}
