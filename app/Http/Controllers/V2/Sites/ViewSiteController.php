<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Sites\SiteResource;
use App\Models\V2\Sites\Site;
use Illuminate\Http\Request;

class ViewSiteController extends Controller
{
    public function __invoke(Request $request, Site $site): SiteResource
    {
        $this->authorize('read', $site);

        return new SiteResource($site);
    }
}
