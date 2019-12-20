<?php

namespace App\Models;

use App\Models\Contracts\NamedEntity;
use App\Models\Contracts\Version;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\SetAttributeDynamicallyTrait;
use App\Models\Traits\SearchScopeTrait;
use Exception;

class PitchVersion extends Model implements Version, NamedEntity
{
    use NamedEntityTrait,
        SetAttributeDynamicallyTrait,
        SearchScopeTrait;

    public $guarded = [];
    public $casts = [
        "land_types" => "array",
        "land_ownerships" => "array",
        "restoration_methods" => "array",
        "restoration_goals" => "array",
        "funding_sources" => "array",
        "revenue_drivers" => "array",
        "sustainable_development_goals" => "array",
        "long_term_engagement" => "boolean"
    ];
    public $dates = [
        "approved_rejected_at"
    ];
    public $timestamps = false;


    public function setAvatarAttribute($avatar): void
    {
        $this->setAttributeDynamically("avatar", $avatar);
    }

    public function setCoverPhotoAttribute($coverPhoto): void
    {
        $this->setAttributeDynamically("cover_photo", $coverPhoto);
    }

    public function setVideoAttribute($video): void
    {
        $this->setAttributeDynamically("video", $video);
    }

    /**
     * This method stops anyone from using the price_per_tree column on the pitch_versions table. This column is
     * computed every time a tree_species_versions row is approved, and is therefore read only. It should not be updated
     * manually! It is far from ideal to denormalise our database, but this column is required when filtering by
     * price_per_tree. Without it we are required to do subqueries for every row in the pitch_versions table, which is
     * not scalable at all. By calculating the price_per_tree from multiple tree_species_versions once, we almost
     * "cache" it in this column and can then quickly filter by it. Consumers of the API are never aware this field even
     * exists... This is the best solution I have at the moment which doesn't cripple the database.
     */
    public function setPricePerTreeAttribute(float $pricePerTree): void
    {
        throw new Exception();
    }
}
