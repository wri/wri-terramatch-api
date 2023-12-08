<?php

namespace App\Helpers;

use App\Models\Reporting\ControlSiteReport;
use App\Models\Reporting\ControlSiteSubmissionReport;
use App\Models\Reporting\CustomReportInterface;
use App\Models\Reporting\ProgrammeReport;
use App\Models\Reporting\ProgrammeSubmissionReport;
use App\Models\Reporting\SiteReport;
use App\Models\Reporting\SiteSubmissionReport;

class CustomReportHelper
{
    public static function availableProperties($key): ?array
    {
        switch ($key) {
            case 'control_site':
                $reporter = new ControlSiteReport();

                break;
            case 'control_site_submission':
                $reporter = new ControlSiteSubmissionReport();

                break;
            case 'programme':
                $reporter = new ProgrammeReport();

                break;
            case 'submission':
                $reporter = new ProgrammeSubmissionReport();

                break;
            case 'site':
                $reporter = new SiteReport();

                break;
            case 'site_submission':
                $reporter = new SiteSubmissionReport();

                break;
            default:
                return null;
        }

        return $reporter->availableFields();
    }

    public static function getReport(string $type): ?CustomReportInterface
    {
        switch ($type) {
            case 'control_site':
                return new ControlSiteReport();
            case 'control_site_submission':
                return new ControlSiteSubmissionReport();
            case 'programme':
                return new ProgrammeReport();
            case 'submission':
                return new ProgrammeSubmissionReport();
            case 'site':
                return new SiteReport();
            case 'site_submission':
                return new SiteSubmissionReport();
            default:
                return null;
        }
    }
}
