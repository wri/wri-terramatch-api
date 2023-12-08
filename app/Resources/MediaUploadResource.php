<?php

namespace App\Resources;

use App\Models\MediaUpload;

class MediaUploadResource extends Resource
{
    public function __construct(MediaUpload $media)
    {
        $this->id = $media->id;
        $this->is_public = $media->is_public;
        $this->programme_id = $media->programme_id;
        $this->site_id = $media->site_id;
        $this->upload = $media->upload;
        $this->location_long = $media->location_long;
        $this->location_lat = $media->location_lat;
        $this->created_at = $media->created_at;
    }
}
