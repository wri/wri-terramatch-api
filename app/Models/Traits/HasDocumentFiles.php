<?php

namespace App\Models\Traits;

use App\Models\DocumentFile;

trait HasDocumentFiles
{
    public function documentFiles()
    {
        return $this->morphMany(DocumentFile::class, 'document_fileable');
    }

    public function getDocumentFileCollection(array $includes)
    {
        if (empty($includes)) {
            return [];
        }

        return $this->documentFiles()->whereIn('collection', $includes)->get();
    }

    public function getDocumentFileExcludingCollection(array $excludes)
    {
        if (empty($excludes)) {
            return [];
        }

        return $this->documentFiles()->whereNotIn('collection', $excludes)->get();
    }
}
