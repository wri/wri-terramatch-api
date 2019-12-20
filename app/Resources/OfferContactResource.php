<?php

namespace App\Resources;

use App\Models\OfferContact as OfferContactModel;
use Exception;

class OfferContactResource
{
    public $id = null;
    public $offer_id = null;
    // public $team_member_id = null;
    // public $user_id = null;

    public function __construct(OfferContactModel $offerContact)
    {
        $this->id = $offerContact->id;
        $this->offer_id = $offerContact->offer_id;
        /**
         * This sections dynamically sets either the team_member_id property or
         * the user_id property depending on the data coming from the model. By
         * doing it this way we avoid a redundant null value being returned.
         */
        $hasTeamMember = !is_null($offerContact->team_member_id);
        $hasUser = !is_null($offerContact->user_id);
        if ($hasTeamMember && !$hasUser) {
            $this->team_member_id = $offerContact->team_member_id;
        } else if (!$hasTeamMember && $hasUser) {
            $this->user_id = $offerContact->user_id;
        } else {
            throw new Exception();
        }
    }
}