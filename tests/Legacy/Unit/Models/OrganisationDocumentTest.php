<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Organisation;
use App\Models\OrganisationDocument;
use Tests\Legacy\LegacyTestCase;

final class OrganisationDocumentTest extends LegacyTestCase
{
    public function testOrganisationDocumentBelongsToOrganisation(): void
    {
        $organisationDocument = OrganisationDocument::first();

        $this->assertInstanceOf(Organisation::class, $organisationDocument->organisation);
    }
}
