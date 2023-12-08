<?php

namespace App\Resources;

use App\Helpers\UploadHelper;
use App\Models\DocumentFile;

class DocumentFileResource extends Resource
{
    public function __construct(DocumentFile $documentFile)
    {
        $this->id = $documentFile->id;
        $this->uuid = $documentFile->uuid;
        $this->upload = $documentFile->upload;
        $this->title = $documentFile->title;
        $this->type = $this->getFileType($documentFile);
        $this->collection = $documentFile->collection;
        $this->is_public = $documentFile->is_public;
        $this->created_at = $documentFile->created_at->toISOString();
    }

    private function getFileType(DocumentFile $documentFile)
    {
        $extension = pathinfo($documentFile->upload, PATHINFO_EXTENSION);

        if (in_array($extension, UploadHelper::IMAGES)) {
            return 'image';
        }
        if (in_array($extension, UploadHelper::VIDEOS)) {
            return 'video';
        }
        if (in_array($extension, UploadHelper::FILES)) {
            return 'file';
        }
    }
}
