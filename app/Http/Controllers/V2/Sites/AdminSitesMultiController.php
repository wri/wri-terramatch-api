<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Sites\SitesCollection;
use App\Models\V2\Sites\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSitesMultiController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('readAll', Site::class);

        if (! empty($request->query('ids'))) {
            $collection = Site::whereIn('uuid', explode(',', $request->query('ids')))->get();

            if ($collection->count() > 0) {
                return new SitesCollection($collection);
            }

            return new JsonResponse('No records found.', 404);
        }

        return new JsonResponse('No uuids provided.', 406);
    }
}
