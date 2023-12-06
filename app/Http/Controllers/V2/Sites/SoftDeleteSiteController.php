<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SoftDeleteSiteController extends Controller
{
    public function __invoke(Request $request, Site $site): JsonResponse
    {
        $this->authorize('delete', $site);

        if ($site->site_reports_total > 0) {
            return new JsonResponse('You can only delete sites that do not have reports', 406);
        }

        $site->delete();

        return new JsonResponse('Site succesfully deleted', 200);
    }
}
