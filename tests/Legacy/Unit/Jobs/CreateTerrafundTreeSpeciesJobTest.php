<?php

namespace Tests\Legacy\Unit\Jobs;

use App\Jobs\CreateTerrafundTreeSpeciesJob;
use App\Models\Terrafund\TerrafundProgramme;
use Tests\Legacy\LegacyTestCase;

final class CreateTerrafundTreeSpeciesJobTest extends LegacyTestCase
{
    public function testJobCreatesTreeSpecies(): void
    {
        CreateTerrafundTreeSpeciesJob::dispatchSync('tree name', 123, TerrafundProgramme::class, 1, 1);

        $this->assertDatabaseHas('terrafund_tree_species', [
            'treeable_type' => TerrafundProgramme::class,
            'treeable_id' => 1,
            'terrafund_csv_import_id' => 1,
            'name' => 'tree name',
            'amount' => 123,
        ]);
    }
}
