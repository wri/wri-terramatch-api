<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Model;

class ElevatorVideo extends Model
{
    use SetAttributeByUploadTrait;
    use NamedEntityTrait;

    public $guarded = [];

    public function setIntroductionAttribute($introduction): void
    {
        $this->setAttributeByUpload('introduction', $introduction);
    }

    public function setAimsAttribute($aims): void
    {
        $this->setAttributeByUpload('aims', $aims);
    }

    public function setImportanceAttribute($importance): void
    {
        $this->setAttributeByUpload('importance', $importance);
    }

    public function upload()
    {
        return $this->belongsTo(\App\Models\Upload::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
