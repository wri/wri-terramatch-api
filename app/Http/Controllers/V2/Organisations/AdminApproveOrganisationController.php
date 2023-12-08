<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Events\V2\Organisation\OrganisationApprovedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Organisations\ApproveRejectOrganisationRequest;
use App\Http\Resources\V2\Organisation\OrganisationResource;
use App\Models\V2\Organisation;

class AdminApproveOrganisationController extends Controller
{
    public function __invoke(ApproveRejectOrganisationRequest $request): OrganisationResource
    {
        $this->authorize('approveReject', Organisation::class);

        $organisation = Organisation::where('uuid', $request->get('uuid'))->firstOrFail();

        $organisation->status = Organisation::STATUS_APPROVED;
        $organisation->save();

        OrganisationApprovedEvent::dispatch($request->user(), $organisation);

        return new OrganisationResource($organisation);
    }
}
