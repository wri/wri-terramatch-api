<?php

namespace App\Models;

use App\Models\Traits\SearchScopeTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\SetAttributeDynamicallyTrait;

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
        "long_term_engagement" => "boolean",
        "completed" => "boolean",
        "successful" => "boolean"
    ];
    public $dates = [
        "completed_at"
    ];

    use SearchScopeTrait;
    use SetAttributeDynamicallyTrait;

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
}
