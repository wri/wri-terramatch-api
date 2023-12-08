<?php

namespace App\Http\Controllers\V2\Files\Location;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Files\Gallery\GallerysLiteCollection;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProjectImageLocationsController extends Controller
{
    public function __invoke(Request $request, Project $project): GallerysLiteCollection
    {
        $this->authorize('read', $project);

        $models = [
            ['type' => get_class($project), 'ids' => [$project->id]],
            ['type' => Site::class, 'ids' => $project->sites->pluck('id')->toArray()],
            ['type' => Nursery::class, 'ids' => $project->nurseries->pluck('id')->toArray()],
            ['type' => ProjectReport::class, 'ids' => $project->reports->pluck('id')->toArray()],
            ['type' => SiteReport::class, 'ids' => $project->siteReports->pluck('id')->toArray()],
            ['type' => NurseryReport::class, 'ids' => $project->nurseryReports->pluck('id')->toArray()],
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
