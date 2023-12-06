<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Events\V2\Organisation\OrganisationSubmittedEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Organisation\OrganisationResource;
use App\Http\Validators\OrganisationSubmitValidation;
use App\Models\V2\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganisationSubmitController extends Controller
{
    public function __invoke(Organisation $organisation, Request $request): OrganisationResource
    {
        $this->authorize('submit', $organisation);

        $validator = Validator::make($organisation->toArray(), (new OrganisationSubmitValidation())->rules());
        $validator->validate();

        $organisation->status = Organisation::STATUS_PENDING;
        $organisation->save();

        OrganisationSubmittedEvent::dispatch($organisation);

        return new OrganisationResource($organisation);
    }
}
