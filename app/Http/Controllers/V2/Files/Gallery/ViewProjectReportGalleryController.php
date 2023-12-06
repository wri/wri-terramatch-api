<?php

namespace App\Http\Controllers\V2\Files\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Files\Gallery\GallerysCollection;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ViewProjectReportGalleryController extends Controller
{
    public function __invoke(Request $request, ProjectReport $projectReport): GallerysCollection
    {
        $this->authorize('read', $projectReport);

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);

        $siteIds = $projectReport->project->sites()->pluck('id')->toArray();
        $nurseryIds = $projectReport->project->nurseries()->pluck('id')->toArray();
        if (empty($this->due_at)) {
            $month = now()->month;
            $year = now()->year;
        } else {
            $month = $this->due_at->month;
            $year = $this->due_at->year;
        }

        $models = [];
        ! empty($entity) && $entity != 'project-reports' ?:
            $models[] = ['type' => ProjectReport::class, 'ids' => [$projectReport->id]];

        ! empty($entity) && $entity != 'site-reports' ?:
            $models[] = [
                'type' => SiteReport::class,
                'ids' => SiteReport::whereIn('site_id', $siteIds)
                    ->whereMonth('due_at', $month)
                    ->whereYear('due_at', $year)
                    ->pluck('id')
                    ->toArray(),
            ];

        ! empty($entity) && $entity != 'nursery-reports' ?:
            $models[] = [
                'type' => NurseryReport::class,
                'ids' => NurseryReport::whereIn('nursery_id', $nurseryIds)
                    ->whereMonth('due_at', $month)
                    ->whereYear('due_at', $year)
                    ->pluck('id')
                    ->toArray(),
            ];

        $mediaQueryBuilder = Media::query()->where(function ($query) use ($models) {
            foreach ($models as $model) {
                $query->orWhere(function ($query) use ($model) {
                    $query->where('model_type', $model['type'])
                        ->whereIn('model_id', $model['ids']);
                });
            }
        });

        //        $mediaQueryBuilder = Media::query()
        //            ->where('model_type', '=', get_class($projectReport))
        //            ->where('model_id', '=', $projectReport->id);

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
