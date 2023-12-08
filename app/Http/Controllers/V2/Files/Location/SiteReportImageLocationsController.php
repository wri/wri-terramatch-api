<?php

namespace App\Http\Controllers\V2\Files\Location;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Files\Gallery\GallerysLiteCollection;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SiteReportImageLocationsController extends Controller
{
    public function __invoke(Request $request, SiteReport $siteReport): GallerysLiteCollection
    {
        $this->authorize('read', $siteReport);

        $collection = Media::query()
            ->where('file_type', 'media')
            ->where('lat', '!=', 0)
            ->where('lng', '!=', 0)
            ->whereNotNull(['lat', 'lng'])
            ->where('model_type', '=', get_class($siteReport))
            ->where('model_id', '=', $siteReport->id)
            ->get();

        return new GallerysLiteCollection($collection);
    }
}
