<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use App\Services\Search\SearchScopeTrait;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    public $guarded = [];
    public $casts = [
        "land_types" => "array",
        "land_ownerships" => "array",
        "restoration_methods" => "array",
        "restoration_goals" => "array",
        "funding_sources" => "array",
        "sustainable_development_goals" => "array",
        "long_term_engagement" => "boolean"
    ];

    use SearchScopeTrait;
    use SetAttributeByUploadTrait;
    use NamedEntityTrait;

    public function setCoverPhotoAttribute($coverPhoto): void
    {
        $this->setAttributeByUpload("cover_photo", $coverPhoto);
    }

    public function setVideoAttribute($video): void
    {
        $this->setAttributeByUpload("video", $video);
    }

    public function organisation()
    {
        return $this->belongsTo("App\\Models\\Organisation");
    }
}
