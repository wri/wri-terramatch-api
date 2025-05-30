<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Traits\HasProjectCoverImage;
use App\Models\V2\Sites\SitePolygon;
use App\Services\PolygonService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class CountryAndPolygonDataController extends Controller
{
    use HasProjectCoverImage;

    public function getPolygonData(string $uuid)
    {
        $polygonData = App::make(PolygonService::class)->getPolygonData($uuid);

        return response()->json($polygonData);
    }
}
