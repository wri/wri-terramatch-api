<?php

namespace App\Observers;

use App\Models\V2\Organisation;

class OrganisationObserver
{
    public function retrieved(Organisation $organisation): void
    {
        $this->updateFinancialIndicators($organisation);
    }

    private function updateFinancialIndicators(Organisation $organisation): void
    {
        if ($organisation->financialReports()->exists()) {
            $organisation->updateFinancialReportsToOrganisation();
        }
    }
}
