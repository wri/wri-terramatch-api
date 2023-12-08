<?php

namespace Tests\Legacy\Unit\Jobs;

use App\Jobs\RemoveSiteTreeSpeciesByImportJob;
use Tests\Legacy\LegacyTestCase;

final class RemoveSiteTreeSpeciesByImportJobTest extends LegacyTestCase
{
    public function testJobDeletesSiteTreeSpeciesByCsvImport(): void
    {
        $this->assertDatabaseHas('site_tree_species', [
            'id' => 2,
            'site_csv_import_id' => 1,
        ]);

        RemoveSiteTreeSpeciesByImportJob::dispatchSync(1);

        $this->assertDatabaseMissing('site_tree_species', [
            'id' => 2,
        ]);
    }
}
