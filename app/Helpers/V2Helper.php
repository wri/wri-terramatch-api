<?php

namespace App\Helpers;

use App\Models\Framework;
use App\Models\Organisation;
use App\Models\Programme;
use App\Models\Site;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundSite;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class V2Helper
{
    public static function getModel(string $type, int $id): ?EloquentModel
    {
        switch ($type) {
            case 'programme':
            case 'project':
            case Programme::class:
                return Programme::find($id);

            case 'site':
            case Site::class:
                return Site::find($id);

            case 'terrafund_site':
            case TerrafundSite::class:
                return TerrafundSite::find($id);

            case 'terrafund_nursery':
            case TerrafundNursery::class:
                return TerrafundNursery::find($id);

            default:
                return null;
        }
    }

    public static function getOrganisation(EloquentModel $model): ?Organisation
    {
        switch (get_class($model)) {
            case Programme::class:
                return $model->organisation;
            case Site::class:
                return $model->programme->organisation;
            case TerrafundSite::class:
            case TerrafundNursery::class:
                return $model->terrafundProgramme->organisation;
            default:
                return null;
        }
    }

    public static function getProject(EloquentModel $model): ?EloquentModel
    {
        switch (get_class($model)) {
            case Programme::class:
                return $model;
            case Site::class:
                return $model->programme;
            case TerrafundSite::class:
            case TerrafundNursery::class:
                return $model->terrafundProgramme;
            default:
                return null;
        }
    }

    public static function getFramework(EloquentModel $model): ?Framework
    {
        switch (get_class($model)) {
            case Programme::class:
            case Site::class:
                return Framework::where('name', 'PPC')->first();
            case TerrafundSite::class:
            case TerrafundNursery::class:
                return Framework::where('name', 'Terrafund')->first();
            default:
                return null;
        }
    }
}
