<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Organisation\OrganisationsCollection;
use App\Models\V2\Organisation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrganisationMultiController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('readAll', Organisation::class);

        if (! empty($request->query('ids'))) {
            $collection = Organisation::whereIn('uuid', explode(',', $request->query('ids')))->get();

            if ($collection->count() > 0) {
                return new OrganisationsCollection($collection);
            }

            return new JsonResponse('No records found.', 404);
        }

        return new JsonResponse('No uuids provided.', 406);
    }
}
