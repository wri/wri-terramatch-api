<?php

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyTreeSpeciesCollections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        TreeSpecies::chunk(500, function ($chunk) use (&$count, &$created) {
            foreach ($chunk as $treeSpecies) {
                if (in_array($treeSpecies->speciesable_type, [Nursery::class, NurseryReport::class])) {
                    $treeSpecies->timestamps = false;
                    $treeSpecies->collection = TreeSpecies::COLLECTION_NURSERY;
                    $treeSpecies->save();
                } else {
                    if (in_array($treeSpecies->collection, ['tree', 'primary']) || empty($treeSpecies->collection)) {
                        $treeSpecies->timestamps = false;
                        $treeSpecies->collection = TreeSpecies::COLLECTION_PLANTED;
                        $treeSpecies->save();
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tableList as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('update_request_status');
            });
        }
    }
}
