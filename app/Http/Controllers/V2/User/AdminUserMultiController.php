<?php

namespace App\Http\Controllers\V2\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\User\UsersCollection;
use App\Models\V2\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserMultiController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('readAll', User::class);

        if (! empty($request->query('ids'))) {
            $collection = User::whereIn('uuid', explode(',', $request->query('ids')))->get();

            if ($collection->count() > 0) {
                return new UsersCollection($collection);
            }

            return new JsonResponse('No records found.', 404);
        }

        return new JsonResponse('No uuids provided.', 406);
    }
}
