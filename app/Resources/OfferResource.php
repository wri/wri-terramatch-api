<?php

namespace App\Resources;

use App\Models\Offer as OfferModel;

class OfferResource extends Resource
{
    public $id = null;
    public $organisation_id = null;
    public $name = null;
    public $description = null;
    public $land_types = [];
    public $land_ownerships = [];
    public $land_size = null;
    public $land_continent = null;
    public $land_country = null;
    public $restoration_methods = [];
    public $restoration_goals = [];
    public $funding_sources = [];
    public $funding_amount = null;
    public $price_per_tree = null;
    public $long_term_engagement = null;
    public $reporting_frequency = null;
    public $reporting_level = null;
    public $sustainable_development_goals = null;
    public $avatar = null;
    public $cover_photo = null;
    public $video = null;
    public $created_at = null;

    public function __construct(OfferModel $offer, bool $displayCompatibilityScore = false)
    {
        $this->id = $offer->id;
        $this->organisation_id = $offer->organisation_id;
        $this->name = $offer->name;
        $this->description = $offer->description;
        $this->land_types = $offer->land_types;
        $this->land_ownerships = $offer->land_ownerships;
        $this->land_size = $offer->land_size;
        $this->land_continent = $offer->land_continent;
        $this->land_country = $offer->land_country;
        $this->restoration_methods = $offer->restoration_methods;
        $this->restoration_goals = $offer->restoration_goals;
        $this->funding_sources = $offer->funding_sources;
        $this->funding_amount = $offer->funding_amount;
        $this->price_per_tree = $offer->price_per_tree;
        $this->long_term_engagement = $offer->long_term_engagement;
        $this->reporting_frequency = $offer->reporting_frequency;
        $this->reporting_level = $offer->reporting_level;
        $this->sustainable_development_goals = $offer->sustainable_development_goals;
        $this->avatar = $offer->avatar;
        $this->cover_photo = $offer->cover_photo;
        $this->video = $offer->video;
        $this->created_at = $offer->created_at;
        $this->completed = $offer->completed;
        $this->completed_by = $offer->completed_by;
        $this->completed_at = $offer->completed_at;
        $this->successful = $offer->successful;
        if ($displayCompatibilityScore) {
            $this->compatibility_score = (int) $offer->compatibility_score;
        }
    }
}