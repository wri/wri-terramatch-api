<?php

namespace App\Resources;

use App\Models\SubmissionMediaUpload;

class SubmissionMediaUploadResource extends Resource
{
    public const IMAGES = ['png', 'jpg', 'gif'];
    public const VIDEOS = ['mp4', 'mov', '3gp'];
    public const FILES = ['pdf', 'xlsx', 'xls', 'csv', 'pdf', 'text'];

    public function __construct(SubmissionMediaUpload $media)
    {
        $this->id = $media->id;
        $this->type = $this->getMediaType($media);
        $this->is_public = $media->is_public;
        $this->submission_id = $media->submission_id;
        $this->upload = $media->upload;
        $this->location_long = $media->location_long;
        $this->location_lat = $media->location_lat;
        $this->created_at = $media->created_at;
    }

    private function getMediaType(SubmissionMediaUpload $media)
    {
        $extension = pathinfo($media->upload, PATHINFO_EXTENSION);

        if (in_array($extension, self::IMAGES)) {
            return 'image';
        }
        if (in_array($extension, self::VIDEOS)) {
            return 'video';
        }
        if (in_array($extension, self::FILES)) {
            return 'application';
        }
    }
}
