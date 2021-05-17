<?php

namespace App\Models;

use App\Models\Interfaces\Version;
use App\Models\Traits\IsVersion;
use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use App\Services\Search\SearchScopeTrait;
use Illuminate\Database\Eloquent\Model;

class PitchVersion extends Model implements Version
{
    use NamedEntityTrait, IsVersion, SetAttributeByUploadTrait, SearchScopeTrait;

    protected $parentClass = "App\\Models\\Pitch";

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

    public function setCoverPhotoAttribute($coverPhoto): void
    {
        $this->setAttributeByUpload("cover_photo", $coverPhoto);
    }

    public function setVideoAttribute($video): void
    {
        $this->setAttributeByUpload("video", $video);
    }

    /**
     * This method stops anyone from using the price_per_tree column on the pitch_versions table. This column is
     * computed every time a tree_species_versions row is approved, and is therefore read only. It should not be updated
     * manually! It is far from ideal to denormalise our database, but this column is required when filtering by
     * price_per_tree. Without it we are required to do subqueries for every row in the pitch_versions table, which is
     * not scalable at all. By calculating the price_per_tree from multiple tree_species_versions once, we almost
     * "cache" it in this column and can then quickly filter by it. This is the best solution I have at the moment which
     * doesn't cripple the database.
     */
    public function setPricePerTreeAttribute($pricePerTree): void
    {
    }
}
