<?php

namespace Tests\Legacy\Unit\Jobs;

use App\Jobs\CreateProgrammeTreeSpeciesJob;
use Tests\Legacy\LegacyTestCase;

final class CreateProgrammeTreeSpeciesJobTest extends LegacyTestCase
{
    public function testJobCreatesProgrammeSpeciesTrees(): void
    {
        CreateProgrammeTreeSpeciesJob::dispatchSync('tree name', 1, 2);

        $this->assertDatabaseHas('programme_tree_species', [
            'programme_id' => 1,
            'name' => 'tree name',
        ]);
    }
}
