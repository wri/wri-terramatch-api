<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Organisation;
use App\Models\OrganisationVersion;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class OrganisationVersionTest extends LegacyTestCase
{
    #[Test]
    public function testOrganisationVersionBelongsToOrganisation(): void
    {
        $organisationVersion = OrganisationVersion::first();

        $this->assertInstanceOf(Organisation::class, $organisationVersion->organisation);
    }
}
