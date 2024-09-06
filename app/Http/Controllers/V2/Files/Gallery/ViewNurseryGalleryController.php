<?php

namespace App\Http\Controllers\V2\Files\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Files\Gallery\GallerysCollection;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ViewNurseryGalleryController extends Controller
{
    public function __invoke(Request $request, Nursery $nursery): GallerysCollection
    {
        $this->authorize('read', $nursery);

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $entity = $request->query('model_name');

        $models = [];
        ! empty($entity) && $entity != 'nurseries' ?: $models[] = ['type' => get_class($nursery), 'ids' => [$nursery->id]];
        ! empty($entity) && $entity != 'nursery-reports' ?: $models[] = ['type' => NurseryReport::class, 'ids' => $nursery->reports()->pluck('id')->toArray()];

        $mediaQueryBuilder = Media::query()->where(function ($query) use ($models) {
            foreach ($models as $model) {
                $query->orWhere(function ($query) use ($model) {
                    $query->where('model_type', $model['type'])
                        ->whereIn('model_id', $model['ids']);
                });
            }
        });

        // Map model types to classes
        $modelTypeMap = [
            'nurseries' => [Nursery::class],
            'reports' => [NurseryReport::class],
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
            ]);

        $collection = $query->paginate($perPage)
            ->appends(request()->query());

        return new GallerysCollection($collection);
    }
}
