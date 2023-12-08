<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidFrameworkInviteCodeException;
use App\Helpers\JsonResponseHelper;
use App\Http\Requests\StoreFrameworkInviteCodeRequest;
use App\Models\FrameworkInviteCode;
use App\Resources\FrameworkInviteCodeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrameworkInviteCodeController extends Controller
{
    public function joinAction(Request $request): JsonResponse
    {
        $this->authorize('read', FrameworkInviteCode::class);

        $inviteCode = FrameworkInviteCode::where('code', $request->get('code'))
            ->first();

        if (! $inviteCode) {
            throw new InvalidFrameworkInviteCodeException();
        }

        Auth::user()->frameworks()->syncWithoutDetaching([$inviteCode->framework_id]);

        return JsonResponseHelper::success(new FrameworkInviteCodeResource($inviteCode), 200);
    }

    public function createAction(StoreFrameworkInviteCodeRequest $request): JsonResponse
    {
        $this->authorize('create', FrameworkInviteCode::class);
        $data = $request->json()->all();

        $inviteCode = new FrameworkInviteCode();
        $inviteCode->code = $data['code'];
        switch ($data['framework']) {
            case 'ppc':
                $inviteCode->framework_id = 1;

                break;
            case 'terrafund':
                $inviteCode->framework_id = 2;

                break;
        }
        $inviteCode->saveOrFail();

        return JsonResponseHelper::success(new FrameworkInviteCodeResource($inviteCode), 201);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize('readAll', FrameworkInviteCode::class);
        $codes = FrameworkInviteCode::all();

        foreach ($codes as $code) {
            $resources[] = new FrameworkInviteCodeResource($code);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $inviteCode = FrameworkInviteCode::findOrFail($id);
        $this->authorize('delete', $inviteCode);
        $inviteCode->delete();

        return JsonResponseHelper::success((object) [], 200);
    }
}
