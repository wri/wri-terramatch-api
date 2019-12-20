<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\SetAttributeDynamicallyTrait;

class TeamMember extends Model
{
    public $timestamps = false;
    public $guarded = [];

    use SetAttributeDynamicallyTrait;

    public function setAvatarAttribute($avatar): void
    {
        $this->setAttributeDynamically("avatar", $avatar);
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
