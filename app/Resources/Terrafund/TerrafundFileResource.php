<?php

namespace App\Resources\Terrafund;

use App\Helpers\UploadHelper;
use App\Models\Terrafund\TerrafundFile as TerrafundFileModel;
use App\Resources\Resource;

class TerrafundFileResource extends Resource
{
    public function __construct(TerrafundFileModel $terrafundFile)
    {
        $this->id = $terrafundFile->id;
        $this->fileable_type = $terrafundFile->fileable_type;
        $this->fileable_id = $terrafundFile->fileable_id;
        $this->upload = $terrafundFile->upload;
        $this->type = $this->getMediaType($terrafundFile);
        $this->is_public = $terrafundFile->is_public;
        $this->location_lat = $terrafundFile->location_lat;
        $this->location_long = $terrafundFile->location_long;
        $this->created_at = $terrafundFile->created_at;
        $this->updated_at = $terrafundFile->updated_at;
    }

    private function getMediaType(TerrafundFileModel $terrafundFile)
    {
        $extension = pathinfo($terrafundFile->upload, PATHINFO_EXTENSION);

        if (in_array($extension, UploadHelper::IMAGES)) {
            return 'image';
        }
        if (in_array($extension, UploadHelper::VIDEOS)) {
            return 'video';
        }
    }
}
