<?php

namespace App\Resources;

class MatchResource extends Resource
{
    public function __construct(object $match, array $offerContacts, array $pitchContacts)
    {
        $this->id = $match->id;
        $this->offer_id = $match->offer_id;
        $this->offer_name = $match->offer_name ?? null;
        $this->offer_interest_id = $match->offer_interest_id;
        $this->offer_contacts = $offerContacts;
        $this->pitch_id = $match->pitch_id;
        $this->pitch_name = $match->pitch_name ?? null;
        $this->pitch_interest_id = $match->pitch_interest_id;
        $this->pitch_contacts = $pitchContacts;
        $this->monitoring_id = $match->monitoring_id ?? null;
        $this->matched_at = $match->created_at;
    }
}