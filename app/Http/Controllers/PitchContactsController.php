<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use App\Validators\PitchContactValidator;
use Exception;
use App\Models\Pitch as PitchModel;
use App\Models\PitchContact as PitchContactModel;
use App\Models\TeamMember as TeamMemberModel;
use App\Models\User as UserModel;
use App\Resources\PitchContactResource;
use App\Exceptions\DuplicatePitchContactException;

class PitchContactsController extends Controller
{
    private $jsonResponseFactory = null;
    private $pitchContactValidator = null;
    private $pitchModel = null;
    private $pitchContactModel = null;
    private $userModel = null;
    private $teamMemberModel = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        PitchContactValidator $pitchContactValidator,
        PitchModel $pitchModel,
        PitchContactModel $pitchContactModel,
        UserModel $userModel,
        TeamMemberModel $teamMemberModel
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->pitchContactValidator = $pitchContactValidator;
        $this->pitchModel = $pitchModel;
        $this->pitchContactModel = $pitchContactModel;
        $this->userModel = $userModel;
        $this->teamMemberModel = $teamMemberModel;
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\PitchContact");
        $data = $request->json()->all();
        $this->pitchContactValidator->validate("create", $data);
        $pitch = $this->pitchModel->findOrFail($data["pitch_id"]);
        $this->authorize("update", $pitch);
        /**
         * This section finds the relevant model, asserts the current user has
         * permission to assign them, and then checks the relevant model isn't
         * already assigned. We don't want duplicates.
         */
        if (array_key_exists("user_id", $data)) {
            $model = $this->userModel->findOrFail($data["user_id"]);
            $this->authorize("assign", $model);
            $existingModel = $this->pitchContactModel
                ->where("pitch_id", "=", $pitch->id)
                ->where("user_id", "=", $data["user_id"])
                ->first();
        } else if (array_key_exists("team_member_id", $data)) {
            $model = $this->teamMemberModel->findOrFail($data["team_member_id"]);
            $this->authorize("update", $model);
            $existingModel = $this->pitchContactModel
                ->where("pitch_id", "=", $pitch->id)
                ->where("team_member_id", "=", $data["team_member_id"])
                ->first();
        } else {
            throw new Exception();
        }
        if (!is_null($existingModel)) {
            throw new DuplicatePitchContactException();
        }
        $pitchContact = $this->pitchContactModel->newInstance($data);
        $pitchContact->saveOrFail();
        $pitchContact->refresh();
        return $this->jsonResponseFactory->success(new PitchContactResource($pitchContact), 201);
    }

    public function readAllByPitchAction(Request $request, int $id): JsonResponse
    {
        $pitch = $this->pitchModel->findOrFail($id);
        $this->authorize("read", $pitch);
        $pitchContacts = $this->pitchContactModel->where("pitch_id", "=", $pitch->id)->get();
        $resources = [];
        foreach ($pitchContacts as $pitchContact) {
            $resources[] = new PitchContactResource($pitchContact);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $pitchContact = $this->pitchContactModel->findOrFail($id);
        $this->authorize("delete", $pitchContact);
        $pitchContact->delete();
        return $this->jsonResponseFactory->success((object) [], 200);
    }
}
