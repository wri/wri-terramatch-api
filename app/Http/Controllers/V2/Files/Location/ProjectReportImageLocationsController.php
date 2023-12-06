<?php

namespace App\Http\Controllers\V2\Files\Location;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Files\Gallery\GallerysLiteCollection;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProjectReportImageLocationsController extends Controller
{
    public function __invoke(Request $request, ProjectReport $projectReport): GallerysLiteCollection
    {
        $this->authorize('read', $projectReport);

        $collection = Media::query()
            ->where('file_type', 'media')
            ->where('lat', '!=', 0)
            ->where('lng', '!=', 0)
            ->whereNotNull(['lat', 'lng'])
            ->where('model_type', '=', get_class($projectReport))
            ->where('model_id', '=', $projectReport->id)
            ->get();

        return new GallerysLiteCollection($collection);
    }
}
