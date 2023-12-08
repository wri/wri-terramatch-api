<?php

namespace App\Resources;

use App\Models\Interest as InterestModel;

class InterestResource extends Resource
{
    public function __construct(InterestModel $interest)
    {
        $this->id = $interest->id;
        $this->organisation_id = $interest->organisation_id;
        $this->initiator = $interest->initiator;
        $this->offer_id = $interest->offer_id;
        $this->pitch_id = $interest->pitch_id;
        $this->matched = $interest->has_matched;
        $this->created_at = $interest->created_at;
    }
}
