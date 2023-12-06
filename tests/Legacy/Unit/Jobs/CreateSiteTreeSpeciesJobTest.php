<?php

namespace Tests\Legacy\Unit\Jobs;

use App\Jobs\CreateSiteTreeSpeciesJob;
use Tests\Legacy\LegacyTestCase;

final class CreateSiteTreeSpeciesJobTest extends LegacyTestCase
{
    public function testJobCreatesSiteSpeciesTreesWithAmount(): void
    {
        CreateSiteTreeSpeciesJob::dispatchSync('tree name', 1, 1, 100);

        $this->assertDatabaseHas('site_tree_species', [
            'site_id' => 1,
            'name' => 'tree name',
            'amount' => 100,
        ]);
    }

    public function testJobCreatesSiteSpeciesTreesWithoutAmount(): void
    {
        CreateSiteTreeSpeciesJob::dispatchSync('tree name without amount', 1, 1);

        $this->assertDatabaseHas('site_tree_species', [
            'site_id' => 1,
            'name' => 'tree name without amount',
            'amount' => null,
        ]);
    }
}
