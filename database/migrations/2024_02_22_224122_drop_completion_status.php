<?php

use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MODEL_LIST = [
        ProjectReport::class,
        SiteReport::class,
        NurseryReport::class,
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::MODEL_LIST as $model) {
            $tableName = (new $model())->getTable();
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('completion_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::MODEL_LIST as $model) {
            $tableName = (new $model())->getTable();
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('completion_status')->after('completion')->default('not-started');
            });

            $model::withTrashed()
                ->where('completion', 100)
                ->update(['completion_status' => 'complete']);
            $model::withTrashed()
                ->whereNot('completion', 0)
                ->whereNot('completion', 100)
                ->update(['completion_status' => 'started']);
        }
    }
};
