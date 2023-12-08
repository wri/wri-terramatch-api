<?php

namespace App\Http\Controllers\V2\Files\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Files\Gallery\GallerysCollection;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ViewSiteGalleryController extends Controller
{
    public function __invoke(Request $request, Site $site): GallerysCollection
    {
        $this->authorize('read', $site);

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $entity = $request->query('model_name');

        $models = [];
        ! empty($entity) && $entity != 'sites' ?: $models[] = ['type' => get_class($site), 'ids' => [$site->id]];
        ! empty($entity) && $entity != 'site-reports' ?: $models[] = ['type' => SiteReport::class, 'ids' => $site->reports()->pluck('id')->toArray()];

        $mediaQueryBuilder = Media::query()->where(function ($query) use ($models) {
            foreach ($models as $model) {
                $query->orWhere(function ($query) use ($model) {
                    $query->where('model_type', $model['type'])
                        ->whereIn('model_id', $model['ids']);
                });
            }
        });

        $query = QueryBuilder::for($mediaQueryBuilder)
            ->allowedFilters([
                AllowedFilter::exact('file_type'),
                AllowedFilter::exact('is_public'),
            ]);

        $collection = $query->paginate($perPage)
            ->appends(request()->query());

        return new GallerysCollection($collection);
    }
}
