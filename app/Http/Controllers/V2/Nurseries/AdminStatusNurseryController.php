<?php

namespace App\Http\Controllers\V2\Nurseries;

use App\Events\V2\General\EntityStatusChangeEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateRequests\StatusChangeRequest;
use App\Http\Resources\V2\Nurseries\NurseryResource;
use App\Models\V2\Nurseries\Nursery;
use Illuminate\Http\JsonResponse;

class AdminStatusNurseryController extends Controller
{
    public function __invoke(StatusChangeRequest $request, Nursery $nursery, string $status)
    {
        $data = $request->validated();
        $this->authorize('approve', $nursery);

        switch($status) {
            case 'approve':
                $nursery->update(['status' => Nursery::STATUS_APPROVED,
                    'feedback' => data_get($data, 'feedback'),
                ]);

                break;
            case 'moreinfo':
                $nursery->update([
                    'status' => Nursery::STATUS_NEEDS_MORE_INFORMATION,
                    'feedback' => data_get($data, 'feedback'),
                    'feedback_fields' => data_get($data, 'feedback_fields'),
                ]);

                break;
            default:
                return new JsonResponse('status not supported', 401);
        }

        EntityStatusChangeEvent::dispatch($request->user(), $nursery, $nursery->name, '', $nursery->readable_status);

        return new NurseryResource($nursery);
    }
}
