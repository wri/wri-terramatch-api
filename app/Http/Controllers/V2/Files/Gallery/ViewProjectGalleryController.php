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
        try {
            $this->authorize('read', $project);

            $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
            $entity = $request->query('model_name');
            $searchTerm = $request->query('search');
            $isGeotagged = $request->query('is_geotagged');
            $sortOrder = $request->query('sort_order', 'asc');
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

            if (!empty($searchTerm)) {
                $mediaIds = Media::where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('file_name', 'LIKE', "%{$searchTerm}%")
                    ->pluck('id');

                $siteIds = Site::where('name', 'LIKE', "%{$searchTerm}%")->pluck('id');
                $nurseryIds = Nursery::where('name', 'LIKE', "%{$searchTerm}%")->pluck('id');

                $additionalMediaIds = Media::whereIn('model_type', [Site::class, Nursery::class])
                    ->whereIn('model_id', $siteIds->merge($nurseryIds))
                    ->pluck('id');

                $allMediaIds = $mediaIds->merge($additionalMediaIds)->unique();

                $mediaQueryBuilder->whereIn('media.id', $allMediaIds);
            }

            if ($isGeotagged === '1') {
                $mediaQueryBuilder->whereNotNull('lat')->whereNotNull('lng');
            } elseif ($isGeotagged === '2') {
                $mediaQueryBuilder->whereNull('lat')->whereNull('lng');
            }

            $modelTypeMap = [
                'projects' => [Project::class],
                'sites' => [Site::class],
                'nurseries' => [Nursery::class],
                'project-reports' => [ProjectReport::class],
                'site-reports' => [SiteReport::class],
                'nursery-reports' => [NurseryReport::class],
                'reports' => [ProjectReport::class, SiteReport::class, NurseryReport::class],
            ];

            $query = QueryBuilder::for($mediaQueryBuilder)
                ->allowedFilters([
                    AllowedFilter::exact('file_type'),
                    AllowedFilter::exact('is_public'),
                    AllowedFilter::callback('model_type', function ($query, $value) use ($modelTypeMap) {
                        $classNames = $modelTypeMap[$value] ?? null;
                        if ($classNames) {
                            $query->where(function ($subQuery) use ($classNames) {
                                foreach ($classNames as $className) {
                                    $subQuery->orWhere('model_type', $className);
                                }
                            });
                        }
                    }),
                ])
                ->allowedSorts(['created_at']);
            $query->orderBy('created_at', $sortOrder);    

            $collection = $query->paginate($perPage)
                ->appends(request()->query());

            return new GallerysCollection($collection);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}