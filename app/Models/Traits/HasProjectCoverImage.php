<?php

namespace App\Models\Traits;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasProjectCoverImage
{
    public function getProjectCoverImage(Project $project): ?Media
    {
        $models = [
            ['type' => get_class($project), 'ids' => [$project->id]],
            ['type' => Site::class, 'ids' => $project->sites->pluck('id')->toArray()],
            ['type' => Nursery::class, 'ids' => $project->nurseries->pluck('id')->toArray()],
            ['type' => ProjectReport::class, 'ids' => $project->reports->pluck('id')->toArray()],
            ['type' => SiteReport::class, 'ids' => $project->siteReports->pluck('id')->toArray()],
            ['type' => NurseryReport::class, 'ids' => $project->nurseryReports->pluck('id')->toArray()],
        ];

        $coverMedia = Media::where(function ($query) use ($models) {
            foreach ($models as $model) {
                $query->orWhere(function ($query) use ($model) {
                    $query->where('model_type', $model['type'])
                        ->whereIn('model_id', $model['ids']);
                });
            }
        })
        ->where('is_cover', true)
        ->first();

        if ($coverMedia) {
            return $coverMedia;
        }

        // If no cover image found, the latest image is sent
        return Media::where(function ($query) use ($models) {
            foreach ($models as $model) {
                $query->orWhere(function ($query) use ($model) {
                    $query->where('model_type', $model['type'])
                        ->whereIn('model_id', $model['ids']);
                });
            }
        })
        ->where(function ($query) {
            $query->where('mime_type', 'like', 'image/jpeg')
                ->orWhere('mime_type', 'like', 'image/png');
        })
        ->latest()
        ->first();
    }
}
