<?php

namespace App\Http\Controllers;

use App\Exceptions\DuplicatePitchContactException;
use App\Exceptions\FinalPitchContactException;
use App\Exceptions\InvalidPitchContactException;
use App\Helpers\JsonResponseHelper;
use App\Http\Requests\StorePitchContactRequest;
use App\Models\Pitch as PitchModel;
use App\Models\PitchContact as PitchContactModel;
use App\Models\TeamMember as TeamMemberModel;
use App\Models\User as UserModel;
use App\Resources\PitchContactResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class PitchContactsController extends Controller
{
    public function createAction(StorePitchContactRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\PitchContact::class);
        $data = $request->json()->all();
        $pitch = PitchModel::findOrFail($data['pitch_id']);
        $this->authorize('update', $pitch);
        /**
         * This section finds the relevant model, asserts the current user has
         * permission to assign them, and then checks the relevant model isn't
         * already assigned. We don't want duplicates.
         */
        if (array_key_exists('user_id', $data) && array_key_exists('team_member_id', $data)) {
            throw new InvalidPitchContactException();
        } elseif (array_key_exists('user_id', $data)) {
            $model = UserModel::findOrFail($data['user_id']);
            $this->authorize('assign', $model);
            $existingModel = PitchContactModel::where('pitch_id', '=', $pitch->id)
                ->where('user_id', '=', $data['user_id'])
                ->first();
        } elseif (array_key_exists('team_member_id', $data)) {
            $model = TeamMemberModel::findOrFail($data['team_member_id']);
            $this->authorize('update', $model);
            $existingModel = PitchContactModel::where('pitch_id', '=', $pitch->id)
                ->where('team_member_id', '=', $data['team_member_id'])
                ->first();
        } else {
            throw new InvalidPitchContactException();
        }
        if (! is_null($existingModel)) {
            throw new DuplicatePitchContactException();
        }

        $pitchContact = PitchContactModel::create($data);

        return JsonResponseHelper::success(new PitchContactResource($pitchContact), 201);
    }

    public function readAllByPitchAction(PitchModel $pitch): JsonResponse
    {
        $this->authorize('read', $pitch);
        $pitchContacts = PitchContactModel::where('pitch_id', '=', $pitch->id)->get();
        $resources = [];
        foreach ($pitchContacts as $pitchContact) {
            $resources[] = new PitchContactResource($pitchContact);
        }
        Arr::sort($resources, function ($resource) {
            return $resource->last_name . ' ' . $resource->first_name;
        });

        return JsonResponseHelper::success($resources, 200);
    }

    public function deleteAction(PitchContactModel $pitchContact): JsonResponse
    {
        $this->authorize('delete', $pitchContact);
        $count = PitchContactModel::where('pitch_id', '=', $pitchContact->pitch_id)->count();
        if ($count <= 1) {
            throw new FinalPitchContactException();
        }
        $pitchContact->delete();

        return JsonResponseHelper::success((object) [], 200);
    }
}
