<?php

namespace App\Resources;

class MatchResource extends Resource
{
    public function __construct(object $matched, array $offerContacts, array $pitchContacts)
    {
        $this->id = $matched->id;
        $this->offer_id = $matched->offer_id;
        $this->offer_name = $matched->offer_name ?? null;
        $this->offer_interest_id = $matched->offer_interest_id;
        $this->offer_contacts = $offerContacts;
        $this->pitch_id = $matched->pitch_id;
        $this->pitch_name = $matched->pitch_name ?? null;
        $this->pitch_interest_id = $matched->pitch_interest_id;
        $this->pitch_contacts = $pitchContacts;
        $this->monitoring_id = $matched->monitoring_id ?? null;
        $this->matched_at = $matched->created_at;
    }
}
