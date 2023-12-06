<?php

namespace App\Http\Controllers\V2\Nurseries;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Nurseries\NurseriesCollection;
use App\Models\V2\Nurseries\Nursery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNurseriesMultiController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('readAll', Nursery::class);

        if (! empty($request->query('ids'))) {
            $collection = Nursery::whereIn('uuid', explode(',', $request->query('ids')))->get();

            if ($collection->count() > 0) {
                return new NurseriesCollection($collection);
            }

            return new JsonResponse('No records found.', 404);
        }

        return new JsonResponse('No uuids provided.', 406);
    }
}
