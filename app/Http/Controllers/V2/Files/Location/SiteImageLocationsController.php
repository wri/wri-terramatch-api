<?php

namespace App\Http\Controllers\V2\Files\Location;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Files\Gallery\GallerysLiteCollection;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SiteImageLocationsController extends Controller
{
    public function __invoke(Request $request, Site $site): GallerysLiteCollection
    {
        $this->authorize('read', $site);

        $models = [
            ['type' => get_class($site), 'ids' => [$site->id]],
            ['type' => SiteReport::class, 'ids' => $site->reports->pluck('id')->toArray()],
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
