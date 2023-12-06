<?php

namespace App\Http\Controllers\V2\Nurseries;

use App\Http\Controllers\Controller;
use App\Models\V2\Nurseries\Nursery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SoftDeleteNurseryController extends Controller
{
    public function __invoke(Request $request, Nursery $nursery): JsonResponse
    {
        $this->authorize('delete', $nursery);

        if ($nursery->nursery_reports_total > 0) {
            return new JsonResponse('You can only delete nurseries that do not have reports', 406);
        }

        $nursery->delete();

        return new JsonResponse('Nursery succesfully deleted', 200);
    }
}
