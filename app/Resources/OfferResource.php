<?php

namespace App\Resources;

use App\Models\Offer as OfferModel;

class OfferResource extends Resource
{
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
        $this->funding_bracket = $offer->funding_bracket;
        $this->price_per_tree = $offer->price_per_tree;
        $this->long_term_engagement = $offer->long_term_engagement;
        $this->reporting_frequency = $offer->reporting_frequency;
        $this->reporting_level = $offer->reporting_level;
        $this->sustainable_development_goals = $offer->sustainable_development_goals;
        $this->avatar = $offer->organisation->approved_version->avatar;
        $this->cover_photo = $offer->cover_photo;
        $this->video = $offer->video;
        $this->created_at = $offer->created_at;
        if ($displayCompatibilityScore) {
            $this->compatibility_score = (int) $offer->compatibility_score;
        }
        $this->successful = in_array($offer->visibility, ['fully_invested_funded', 'finished']);
        $this->visibility = $offer->visibility;
    }
}
