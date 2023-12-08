<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\OrganisationDocument;
use App\Models\OrganisationDocumentVersion;
use Tests\Legacy\LegacyTestCase;

final class OrganisationDocumentVersionTest extends LegacyTestCase
{
    public function testOrganisationDocumentVersionBelongsToOrganisationDocument(): void
    {
        $organisationDocumentVersion = OrganisationDocumentVersion::first();

        $this->assertInstanceOf(OrganisationDocument::class, $organisationDocumentVersion->organisationDocument);
    }
}
