<?php

namespace App\Models;

use App\Helpers\UrlHelper;
use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    public $timestamps = false;
    public $guarded = [];

    use SetAttributeByUploadTrait;
    use NamedEntityTrait;

    public function setAvatarAttribute($avatar): void
    {
        $this->setAttributeByUpload("avatar", $avatar);
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
