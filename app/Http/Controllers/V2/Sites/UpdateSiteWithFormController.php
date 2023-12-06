<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HandlesUpdateRequests;
use App\Http\Requests\V2\Forms\UpdateFormSubmissionRequest;
use App\Http\Resources\V2\Sites\SiteResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Sites\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UpdateSiteWithFormController extends Controller
{
    use HandlesUpdateRequests;

    public function __invoke(Site $site, UpdateFormSubmissionRequest $formSubmissionRequest)
    {
        $this->authorize('update', $site);
        $data = $formSubmissionRequest->validated();
        $answers = data_get($data, 'answers', []);

        $form = Form::where('model', Site::class)
            ->where('framework_key', $site->framework_key)
            ->first();

        if (empty($form)) {
            return new JsonResponse('No site form schema found for this framework.', 404);
        }

        if (Auth::user()->can('framework-' . $site->framework_key)) {
            $entityProps = $site->mapEntityAnswers($answers, $form, config('wri.linked-fields.models.site.fields', []));
            $site->update($entityProps);

            $site->status = Site::STATUS_APPROVED;
            $site->save();

            return new SiteResource($site);
        }

        if (! in_array($site->status, [Site::STATUS_AWAITING_APPROVAL, Site::STATUS_NEEDS_MORE_INFORMATION, Site::STATUS_APPROVED])) {
            $entityProps = $site->mapEntityAnswers($answers, $form, config('wri.linked-fields.models.site.fields', []));
            $site->update($entityProps);

            return new SiteResource($site);
        }

        return $this->handleUpdateRequest($site, $answers);
    }
}
