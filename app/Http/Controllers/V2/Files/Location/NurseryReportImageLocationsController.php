<?php

namespace App\Http\Controllers\V2\Files\Location;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Files\Gallery\GallerysLiteCollection;
use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class NurseryReportImageLocationsController extends Controller
{
    public function __invoke(Request $request, NurseryReport $nurseryReport): GallerysLiteCollection
    {
        $this->authorize('read', $nurseryReport);

        $collection = Media::query()
            ->where('file_type', 'media')
            ->where('lat', '!=', 0)
            ->where('lng', '!=', 0)
            ->whereNotNull(['lat', 'lng'])
            ->where('model_type', '=', get_class($nurseryReport))
            ->where('model_id', '=', $nurseryReport->id)
            ->get();

        return new GallerysLiteCollection($collection);
    }
}
