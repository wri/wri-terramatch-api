<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Model;

class ProgressUpdate extends Model
{
    use NamedEntityTrait;

    use SetAttributeByUploadTrait;

    public $fillable = [
        'monitoring_id',
        'grouping',
        'title',
        'breakdown',
        'summary',
        'data',
        'images',
        'created_by',
    ];

    public $casts = [
        'data' => 'array',
        'images' => 'array',
    ];

    public function monitoring()
    {
        return $this->belongsTo(Monitoring::class, 'monitoring_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * This method sets the images attribute. While each image is an upload
     * object, the attribute itself is an array. The setAttributeByUpload
     * trait was only designed for upload objects (not arrays). Therefore we can
     * work around that by setting a temporary attribute using that trait, then
     * immediately extracting it and repacking it into the array. Finally the images
     * are JSON encoded so that they can be stored in a single column.
     */
    public function setImagesAttribute(array $images): Void
    {
        foreach ($images as $key => &$image) {
            $temp = 'images_' . $key . '_image';
            $this->setAttributeByUpload($temp, $image['image']);
            $image['image'] = $this->attributes[$temp];
            unset($this->attributes[$temp]);
        }
        $this->attributes['images'] = json_encode($images);
    }
}
