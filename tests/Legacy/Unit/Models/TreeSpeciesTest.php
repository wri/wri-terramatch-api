<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Pitch;
use App\Models\TreeSpecies;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class TreeSpeciesTest extends LegacyTestCase
{
    #[Test]
    public function testTreeSpeciesBelongsToPitch(): void
    {
        $treeSpecies = TreeSpecies::first();

        $this->assertInstanceOf(Pitch::class, $treeSpecies->pitch);
    }
}
