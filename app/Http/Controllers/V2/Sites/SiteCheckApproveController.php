<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteCheckApproveController extends Controller
{
    public function __invoke(Request $request, string $site): JsonResource
    {
        $nonApproved = SitePolygon::where('site_id', $site)
            ->where('status', '!=', 'approved')
            ->first();

        return new JsonResource(['can_approve' => $nonApproved != null]);
    }
}
