<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Events\V2\Organisation\OrganisationRejectedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Organisations\ApproveRejectOrganisationRequest;
use App\Http\Resources\V2\Organisation\OrganisationResource;
use App\Models\V2\Organisation;

class AdminRejectOrganisationController extends Controller
{
    public function __invoke(ApproveRejectOrganisationRequest $request): OrganisationResource
    {
        $this->authorize('approveReject', Organisation::class);

        $organisation = Organisation::where('uuid', $request->get('uuid'))->firstOrFail();

        $organisation->status = Organisation::STATUS_REJECTED;
        $organisation->save();

        OrganisationRejectedEvent::dispatch($request->user(), $organisation);

        return new OrganisationResource($organisation);
    }
}
