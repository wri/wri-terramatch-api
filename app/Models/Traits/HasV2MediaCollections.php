<?php

namespace App\Models\Traits;

use App\Http\Resources\V2\Files\FileResource;

trait HasV2MediaCollections
{
    public function getFileCollectionList(): array
    {
        return array_keys($this->fileConfiguration);
    }

    public function appendFilesToResource(array $data): array
    {
        foreach ($this->fileConfiguration as $key => $config) {
            if ($config['multiple'] == true) {
                $data[$key] = FileResource::collection($this->getMedia($key));
            } else {
                $data[$key] = new FileResource($this->getMedia($key)->first());
            }
        }

        return $data;
    }

    public function getFileResource($collection, array $colCfg = [])
    {
        if (data_get($colCfg, 'multiple') === true) {
            $files = $this->getMedia($collection);

            return empty($files) ? null : FileResource::collection($files);
        }

        $file = $this->getMedia($collection)->first();

        return empty($file) ? null : new FileResource($file);
    }
}
