<?php

namespace App\Mail;

use Exception;

class VersionApproved extends BaseEmail
{
    public function __construct(String $model, Int $id)
    {
        switch ($model) {
            case "OrganisationVersion":
            case "OrganisationDocumentVersion":
                $link = "/profile";
                break;
            case "CarbonCertificationVersion":
            case "PitchVersion":
            case "PitchDocumentVersion":
            case "RestorationMethodMetricVersion":
            case "TreeSpeciesVersion":
                $link = "/projects/" . $id;
                break;
            default:
                throw new Exception();
        }
        $this->subject = 'Your Changes Have Been Approved';
        $this->title = "Your Changes Have Been Approved";
        $this->body = "Follow this link to view the changes.";
        $this->link = config("app.front_end") . $link;
        $this->cta = "View Changes";
    }
}
