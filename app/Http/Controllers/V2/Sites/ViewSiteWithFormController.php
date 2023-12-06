<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Sites\SiteWithSchemaResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Sites\Site;
use Illuminate\Http\Request;

class ViewSiteWithFormController extends Controller
{
    public function __invoke(Request $request, Site $site): SiteWithSchemaResource
    {
        $this->authorize('read', $site);

        $schema = Form::where('framework_key', $site->framework_key)
            ->where('model', Site::class)
            ->first();

        return new SiteWithSchemaResource($site, ['schema' => $schema]);
    }
}
