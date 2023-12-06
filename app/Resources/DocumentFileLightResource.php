<?php

namespace App\Resources;

use App\Models\DocumentFile;

class DocumentFileLightResource extends Resource
{
    public function __construct(DocumentFile $documentFile)
    {
        $this->title = $documentFile->title;
        $this->url = $documentFile->upload;
        $this->uuid = $documentFile->uuid;
    }
}
