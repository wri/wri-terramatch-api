<?php

namespace App\Mail;

use Exception;

class VersionCreated extends BaseEmail
{
    public function __construct(String $model)
    {
        switch ($model) {
            case "OrganisationVersion":
            case "OrganisationDocumentVersion":
                $link = "/admin/organizations";
                break;
            case "CarbonCertificationVersion":
            case "PitchVersion":
            case "PitchDocumentVersion":
            case "RestorationMethodMetricVersion":
            case "TreeSpeciesVersion":
                $link = "/admin/pitches";
                break;
            default:
                throw new Exception();
        }
        $this->subject = 'Changes Requiring Your Approval';
        $this->title = "Changes Requiring Your Approval";
        $this->body =  "Follow this link to review the changes.";
        $this->link = config("app.front_end") . $link;
        $this->cta = "Review Changes";
    }
}
