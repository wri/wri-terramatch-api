<?php

namespace App\Models;

use App\Helpers\UploadHelper;
use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Model;

class ProgressUpdate extends Model
{
    use NamedEntityTrait;

    public $guarded = [];
    public $casts = [
        "data" => "array",
        "images" => "array"
    ];

    public function monitoring()
    {
        return $this->belongsTo("App\\Models\\Monitoring", "monitoring_id", "id");
    }

    use SetAttributeByUploadTrait;

    /**
     * This method sets the images attribute. While each image is an upload
     * object, the attribute itself is an array. The setAttributeByUpload
     * trait was only designed for upload objects (not arrays). Therefore we can
     * work around that by setting a temporary attribute using that trait, then
     * immediately extracting it and repacking it into the array. Finally the images
     * are JSON encoded so that they can be stored in a single column.
     */
    public function setImagesAttribute(Array $images): Void
    {
        foreach ($images as $key => &$image) {
            $temp = "images_" . $key . "_image";
            $this->setAttributeByUpload($temp, $image["image"]);
            $image["image"] = $this->attributes[$temp];
            unset($this->attributes[$temp]);
        }
        $this->attributes["images"] = json_encode($images);
    }
}
