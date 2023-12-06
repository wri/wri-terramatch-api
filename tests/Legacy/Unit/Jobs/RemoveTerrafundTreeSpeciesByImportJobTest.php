<?php

namespace Tests\Legacy\Unit\Jobs;

use App\Jobs\RemoveTerrafundTreeSpeciesByImportJob;
use Tests\Legacy\LegacyTestCase;

final class RemoveTerrafundTreeSpeciesByImportJobTest extends LegacyTestCase
{
    public function testJobDeletesTreeSpeciesByCsvImport(): void
    {
        $this->assertDatabaseHas('terrafund_tree_species', [
            'id' => 2,
            'terrafund_csv_import_id' => 1,
        ]);

        RemoveTerrafundTreeSpeciesByImportJob::dispatchSync(1);

        $this->assertDatabaseMissing('terrafund_tree_species', [
            'id' => 2,
        ]);
    }
}
