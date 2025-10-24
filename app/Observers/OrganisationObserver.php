<?php

namespace App\Observers;

use App\Models\V2\Organisation;

class OrganisationObserver
{
    public function retrieved(Organisation $organisation): void
    {
        $this->updateFinancialReportsDoc($organisation);
    }

    private function updateFinancialReportsDoc(Organisation $organisation): void
    {
        if ($organisation->financialReports()->exists()) {
            $organisation->updateFinancialReportsDocToOrganisation();
        }
    }
}
