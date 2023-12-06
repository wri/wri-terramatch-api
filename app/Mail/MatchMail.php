<?php

namespace App\Mail;

use Exception;

class MatchMail extends Mail
{
    public function __construct(String $model, String $firstName = "", String $secondName = "")
    {
        switch ($model) {
            case "Admin":
                $isAdmin = true;
                break;
            case "Funder":
            case "Developer":
                $isAdmin = false;
                $isFunder = $model == "Funder";
                break;
            default:
                throw new Exception();
        }
        if ($isAdmin) {
            $this->subject = 'Match Detected';
            $this->title = "Match Detected";
            $this->body =
                e($firstName) . " and " . e($secondName) . " have matched.<br><br>" .
                "Follow this link to view the match.";
            $this->link = "/admin/matches";
            $this->cta = "View Match";
        } else {
            if ($isFunder) {
                $this->subject = 'Someone Has Matched With One Of Your Funding Offers';
                $this->title = "Someone Has Matched With One Of Your Funding Offers";
                $this->body =
                    "Congratulations! " . e($firstName) . " has matched with one of your funding offers.<br><br>" .
                    "Follow the link below to view their contact details.<br><br>" .
                    "If you have decided to move forward together, we encourage you to monitor your project on TerraMatch. Our monitoring system allows you to set mutually agreed targets, easily report on project progress using our templates, and access WRI's state-of-the-art satellite monitoring so that you can track progress over the long term.<br><br>" .
                    "Check out the monitoring section at <a href=\"https://www.TerraMatch.org\" style=\"color: #000000;\">TerraMatch.org</a>.";
            } else {
                $this->subject = 'Someone Has Matched With One Of Your Projects';
                $this->title = "Someone Has Matched With One Of Your Projects";
                $this->body =
                    "Congratulations! " . e($firstName) . " has matched with one of your projects.<br><br>" .
                    "Follow the link below to view their contact details.<br><br>" .
                    "If you have decided to move forward together, we encourage you to monitor your project on TerraMatch. Our monitoring system allows you to set mutually agreed targets, easily report on project progress using our templates, and access WRI's state-of-the-art satellite monitoring to show the funder that your trees are surviving.
<br><br>" .
                    "Check out the monitoring section at <a href=\"https://www.TerraMatch.org\" style=\"color: #000000;\">TerraMatch.org</a>.";
            }
            $this->link = "/connections";
            $this->cta = "View Contact Details";
        }
    }
}
