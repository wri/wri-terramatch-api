<?php

namespace App\Http\Controllers\V2\Files\Location;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Files\Gallery\GallerysLiteCollection;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class NurseryImageLocationsController extends Controller
{
    public function __invoke(Request $request, Nursery $nursery): GallerysLiteCollection
    {
        $this->authorize('read', $nursery);

        $models = [
            ['type' => get_class($nursery), 'ids' => [$nursery->id]],
            ['type' => NurseryReport::class, 'ids' => $nursery->reports->pluck('id')->toArray()],
        ];

        $mediaQueryBuilder = Media::query()
            ->where('file_type', 'media')
            ->where('lat', '!=', 0)
            ->where('lng', '!=', 0)
            ->whereNotNull(['lat', 'lng']);

        $mediaQueryBuilder->where(function ($query) use ($models) {
            foreach ($models as $model) {
                $query->orWhere(function ($query) use ($model) {
                    $query->where('model_type', $model['type'])
                        ->whereIn('model_id', $model['ids']);
                });
            }
        });

        $collection = $mediaQueryBuilder->get();

        return new GallerysLiteCollection($collection);
    }
}
