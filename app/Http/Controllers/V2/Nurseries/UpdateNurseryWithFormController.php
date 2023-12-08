<?php

namespace App\Http\Controllers\V2\Nurseries;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HandlesUpdateRequests;
use App\Http\Requests\V2\Forms\UpdateFormSubmissionRequest;
use App\Http\Resources\V2\Nurseries\NurseyWithSchemaResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\Nursery;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UpdateNurseryWithFormController extends Controller
{
    use HandlesUpdateRequests;

    public function __invoke(Nursery $nursery, UpdateFormSubmissionRequest $formSubmissionRequest)
    {
        $this->authorize('update', $nursery);
        $data = $formSubmissionRequest->validated();
        $answers = data_get($data, 'answers', []);

        $form = Form::where('model', Nursery::class)
            ->where('framework_key', $nursery->framework_key)
            ->first();

        if (empty($form)) {
            return new JsonResponse('No nursery form schema found for this framework.', 404);
        }

        if (Auth::user()->can('framework-' . $nursery->framework_key)) {
            $entityProps = $nursery->mapEntityAnswers($answers, $form, config('wri.linked-fields.models.nursery.fields', []));
            $nursery->update($entityProps);

            $nursery->status = Nursery::STATUS_APPROVED;
            $nursery->save();

            return new NurseyWithSchemaResource($nursery, ['schema' => $form]);
        }

        if (! in_array($nursery->status, [Nursery::STATUS_AWAITING_APPROVAL, Nursery::STATUS_NEEDS_MORE_INFORMATION, Nursery::STATUS_APPROVED])) {
            $entityProps = $nursery->mapEntityAnswers($answers, $form, config('wri.linked-fields.models.nursery.fields', []));
            $nursery->update($entityProps);

            return new NurseyWithSchemaResource($nursery, ['schema' => $form]);
        }

        return $this->handleUpdateRequest($nursery, $answers);
    }
}
