<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\Site;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteCheckApproveController extends Controller
{
    public function __invoke(Request $request, Site $site): JsonResource
    {
        $hasNonApproved = $site->sitePolygons()->where('status', '!=', 'approved')->exists();

        return new JsonResource(['can_approve' => $hasNonApproved]);
    }
}
