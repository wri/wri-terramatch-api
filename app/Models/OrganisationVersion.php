<?php

namespace App\Models;

use App\Models\Interfaces\Version;
use App\Models\Traits\IsVersion;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\SetAttributeByUploadTrait;
use App\Helpers\UrlHelper;

class OrganisationVersion extends Model implements Version
{
    use NamedEntityTrait, SetAttributeByUploadTrait, IsVersion;

    protected $parentClass = "App\\Models\\Organisation";

    public $guarded = [];
    public $dates = [
        "approved_rejected_at",
        "founded_at"
    ];


    public function setAvatarAttribute($avatar): void
    {
        $this->setAttributeByUpload("avatar", $avatar);
    }

    public function setCoverPhotoAttribute($coverPhoto): void
    {
        $this->setAttributeByUpload("cover_photo", $coverPhoto);
    }

    public function setVideoAttribute($video): void
    {
        $this->setAttributeByUpload("video", $video);
    }

    public function setWebsiteAttribute($website): void
    {
        $this->attributes["website"] = UrlHelper::repair($website);
    }

    public function setFacebookAttribute($facebook): void
    {
        $this->attributes["facebook"] = UrlHelper::repair($facebook);
    }

    public function setTwitterAttribute($twitter): void
    {
        $this->attributes["twitter"] = UrlHelper::repair($twitter);
    }

    public function setInstagramAttribute($instagram): void
    {
        $this->attributes["instagram"] = UrlHelper::repair($instagram);
    }

    public function setLinkedinAttribute($linkedin): void
    {
        $this->attributes["linkedin"] = UrlHelper::repair($linkedin);
    }
}
