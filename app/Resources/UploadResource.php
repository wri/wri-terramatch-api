<?php

namespace App\Resources;

use App\Models\Upload as UploadModel;

class UploadResource extends Resource
{
    public function __construct(UploadModel $upload)
    {
        $this->id = $upload->id;
        $this->uploaded_at = $upload->created_at;
    }
}