<?php

namespace App\Http\Controllers\V2\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\User\ActionResource;
use App\Models\V2\Action;

class CompleteActionController extends Controller
{
    public function __invoke(Action $action): ActionResource
    {
        $this->authorize('read', $action);

        $action->update([
            'status' => Action::STATUS_COMPLETE,
        ]);

        return new ActionResource($action);
    }
}
