<?php

namespace App\Models;

use App\Models\Contracts\NamedEntity;
use App\Models\Contracts\Version;
use App\Models\Traits\IsVersion;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\SetAttributeDynamicallyTrait;

class OrganisationVersion extends Model implements Version, NamedEntity
{
    use NamedEntityTrait,
        SetAttributeDynamicallyTrait,
        IsVersion;

    protected $parentClass = Organisation::class;

    public $timestamps = false;
    public $guarded = [];
    public $dates = [
        "approved_rejected_at",
        "founded_at"
    ];


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

    public function setWebsiteAttribute($website): void
    {
        $this->attributes["website"] = repair_url($website);
    }

    public function setFacebookAttribute($facebook): void
    {
        $this->attributes["facebook"] = repair_url($facebook);
    }

    public function setTwitterAttribute($twitter): void
    {
        $this->attributes["twitter"] = repair_url($twitter);
    }

    public function setInstagramAttribute($instagram): void
    {
        $this->attributes["instagram"] = repair_url($instagram);
    }

    public function setLinkedinAttribute($linkedin): void
    {
        $this->attributes["linkedin"] = repair_url($linkedin);
    }
}
