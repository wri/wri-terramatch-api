<?php

namespace App\Observers\V2;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;

class NurseryObserver
{
    public function deleted(Nursery $nursery): void
    {
        $this->deleleNestedNurseryReports($nursery);
    }

    private function deleleNestedNurseryReports(Nursery $nursery): void
    {
        $reports = NurseryReport::where('nursery_id', $nursery->id)->get();
        foreach ($reports as $report) {
            $report->delete();
        }
    }
}
