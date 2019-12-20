<?php

namespace App\Resources;

use Carbon\Carbon;

class MatchResource extends Resource
{
    public $id = null;
    public $offer_id = null;
    public $offer_interest_id = null;
    public $offer_contacts = [];
    public $pitch_id = null;
    public $pitch_interest_id = null;
    public $pitch_contacts = [];
    public $matched_at = null;

    public function __construct(object $match, array $offerContacts, array $pitchContacts)
    {
        $this->id = $match->id;
        $this->offer_id = $match->offer_id;
        $this->offer_interest_id = $match->offer_interest_id;
        $this->offer_contacts = $offerContacts;
        $this->pitch_id = $match->pitch_id;
        $this->pitch_interest_id = $match->pitch_interest_id;
        $this->pitch_contacts = $pitchContacts;
        $this->matched_at = Carbon::createFromFormat("Y-m-d H:i:s", $match->created_at);
    }
}