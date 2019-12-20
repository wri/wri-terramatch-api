<?php

namespace App\Resources;

use App\Models\Interest as InterestModel;

class InterestResource extends Resource
{
    public $id = null;
    public $organisation_id = null;
    public $initiator = null;
    public $offer_id = null;
    public $pitch_id = null;
    public $matched = null;
    public $created_at = null;

    public function __construct(InterestModel $interest)
    {
        $this->id = $interest->id;
        $this->organisation_id = $interest->organisation_id;
        $this->initiator = $interest->initiator;
        $this->offer_id = $interest->offer_id;
        $this->pitch_id = $interest->pitch_id;
        $this->matched = $interest->matched;
        $this->created_at = $interest->created_at;
    }
}
