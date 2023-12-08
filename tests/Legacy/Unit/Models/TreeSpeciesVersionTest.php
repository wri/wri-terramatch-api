<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\TreeSpecies;
use App\Models\TreeSpeciesVersion;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class TreeSpeciesVersionTest extends LegacyTestCase
{
    #[Test]
    public function testTreeSpeciesVersionBelongsToTreeSpecies(): void
    {
        $treeSpeciesVersion = TreeSpeciesVersion::first();

        $this->assertInstanceOf(TreeSpecies::class, $treeSpeciesVersion->treeSpecies);
    }

    #[Test]
    public function treeSpeciesVersionBelongsToApprovedRejectedBy(): void
    {
        $treeSpeciesVersion = TreeSpeciesVersion::first();

        $this->assertInstanceOf(User::class, $treeSpeciesVersion->approvedRejectedBy);
    }
}
