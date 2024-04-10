<?php

namespace App\Models\Traits;

use App\Http\Resources\V2\Entities\EntityWithSchemaResource;
use App\Models\V2\EntityModel;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use ReflectionClass;

trait HasEntityResources
{
    public function createResource(): JsonResource
    {
        // Construct the correct namespace for the resource related to this entity.
        $class = new ReflectionClass(get_class($this));

        $resourceNamespaceParts = explode(
            '\\',
            str_replace('Models', 'Http\Resources', $class->getNamespaceName())
        );
        $partsLastIndex = count($resourceNamespaceParts) - 1;
        if (str_ends_with($class->getShortName(), 'Report')) {
            $resourceNamespaceParts[$partsLastIndex] = Str::singular($resourceNamespaceParts[$partsLastIndex]) . 'Reports';
        }

        $resourceClassName = join('\\', $resourceNamespaceParts) . '\\' . $class->getShortName() . 'Resource';

        return new $resourceClassName($this);
    }

    public function createSchemaResource(): JsonResource
    {
        /** @var EntityModel $this */
        return new EntityWithSchemaResource($this);
    }
}
