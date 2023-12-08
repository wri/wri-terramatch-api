<?php

namespace App\Http\Controllers\V2\Files\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Files\Gallery\GallerysCollection;
use App\Models\V2\Sites\SiteMonitoring;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ViewSiteMonitoringGalleryController extends Controller
{
    public function __invoke(Request $request, SiteMonitoring $siteMonitoring): GallerysCollection
    {
        $this->authorize('read', $siteMonitoring);

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);

        $mediaQueryBuilder = Media::query()
            ->where('model_type', '=', get_class($siteMonitoring))
            ->where('model_id', '=', $siteMonitoring->id);

        $query = QueryBuilder::for($mediaQueryBuilder)
            ->allowedFilters([
                AllowedFilter::exact('file_type'),
                AllowedFilter::exact('is_public'),
                AllowedFilter::trashed(),
            ]);

        $collection = $query->paginate($perPage)
            ->appends(request()->query());

        return new GallerysCollection($collection);
    }
}
