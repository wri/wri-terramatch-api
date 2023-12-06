<?php

namespace App\Http\Controllers\V2\Files\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Files\Gallery\GallerysCollection;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ViewProjectGalleryController extends Controller
{
    public function __invoke(Request $request, Project $project): GallerysCollection
    {
        $this->authorize('read', $project);

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $entity = $request->query('model_name');

        $models = [];
        ! empty($entity) && $entity != 'projects' ?: $models[] = ['type' => get_class($project), 'ids' => [$project->id]];
        ! empty($entity) && $entity != 'sites' ?: $models[] = ['type' => Site::class, 'ids' => $project->sites->pluck('id')->toArray()];
        ! empty($entity) && $entity != 'nurseries' ?: $models[] = ['type' => Nursery::class, 'ids' => $project->nurseries->pluck('id')->toArray()];
        ! empty($entity) && $entity != 'project-reports' ?: $models[] = ['type' => ProjectReport::class, 'ids' => $project->reports->pluck('id')->toArray()];
        ! empty($entity) && $entity != 'site-reports' ?: $models[] = ['type' => SiteReport::class, 'ids' => $project->siteReports->pluck('id')->toArray()];
        ! empty($entity) && $entity != 'nursery-reports' ?: $models[] = ['type' => NurseryReport::class, 'ids' => $project->nurseryReports->pluck('id')->toArray()];

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
