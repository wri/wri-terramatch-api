<?php

namespace Tests\Legacy\Unit\Jobs;

use App\Jobs\RemoveProgrammeTreeSpeciesByImportJob;
use Tests\Legacy\LegacyTestCase;

final class RemoveProgrammeTreeSpeciesByImportJobTest extends LegacyTestCase
{
    public function testJobDeletesProgrammeTreeSpeciesByCsvImport(): void
    {
        $this->assertDatabaseHas('programme_tree_species', [
            'id' => 6,
            'csv_import_id' => 3,
        ])->assertDatabaseHas('programme_tree_species', [
            'id' => 7,
            'csv_import_id' => 3,
        ])->assertDatabaseHas('programme_tree_species', [
            'id' => 8,
            'csv_import_id' => 3,
        ]);

        RemoveProgrammeTreeSpeciesByImportJob::dispatchSync(3);

        $this->assertDatabaseMissing('programme_tree_species', [
            'id' => 6,
        ])->assertDatabaseMissing('programme_tree_species', [
            'id' => 7,
        ])->assertDatabaseMissing('programme_tree_species', [
            'id' => 8,
        ]);
    }
}
