<?php

namespace App\Http\Controllers\V2\Sites;

use App\Events\V2\General\EntityDeleteEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Sites\SiteResource;
use App\Models\V2\Sites\Site;
use Illuminate\Http\Request;

class AdminSoftDeleteSiteController extends Controller
{
    public function __invoke(Request $request, Site $site): SiteResource
    {
        $this->authorize('delete', $site);

        $site->delete();

        EntityDeleteEvent::dispatch($request->user(), $site);
        //EntityDeleteEvent::dispatch($request->user(), $site); to other entitys

        return new SiteResource($site);
    }
}
