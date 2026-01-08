<?php

namespace App\Http\Controllers\V2\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\User\ActionResource;
use App\Models\V2\Action;
use Illuminate\Support\Facades\Auth;

class CompleteActionController extends Controller
{
    public function __invoke(Action $action): ActionResource
    {
        $user = Auth::user();
        if ($user->organisation->id != $action->organisation_id) {
            return new ActionResource($action);
        }
        $this->authorize('read', $action);

        $action->update([
            'status' => Action::STATUS_COMPLETE,
        ]);

        return new ActionResource($action);
    }
}
