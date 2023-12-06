<?php

namespace App\Http\Controllers\V2\Sites;

use App\Events\V2\General\EntityStatusChangeEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateRequests\StatusChangeRequest;
use App\Http\Resources\V2\Sites\SiteResource;
use App\Models\V2\Sites\Site;
use Illuminate\Http\JsonResponse;

class AdminStatusSiteController extends Controller
{
    public function __invoke(StatusChangeRequest $request, Site $site, string $status)
    {
        $data = $request->validated();
        $this->authorize('approve', $site);

        switch($status) {
            case 'approve':
                $site->update(['status' => Site::STATUS_APPROVED,
                    'feedback' => data_get($data, 'feedback'),
                ]);

                break;
            case 'moreinfo':
                $site->update([
                    'status' => Site::STATUS_NEEDS_MORE_INFORMATION,
                    'feedback' => data_get($data, 'feedback'),
                    'feedback_fields' => data_get($data, 'feedback_fields'),
                ]);

                break;
            default:
                return new JsonResponse('status not supported', 401);
        }

        EntityStatusChangeEvent::dispatch($request->user(), $site, $site->name, '', $site->readable_status);

        return new SiteResource($site);
    }
}
