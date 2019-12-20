<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use App\Validators\OfferContactValidator;
use Exception;
use App\Models\Offer as OfferModel;
use App\Models\OfferContact as OfferContactModel;
use App\Models\TeamMember as TeamMemberModel;
use App\Models\User as UserModel;
use App\Resources\OfferContactResource;
use App\Exceptions\DuplicateOfferContactException;

class OfferContactsController extends Controller
{
    private $jsonResponseFactory = null;
    private $offerContactValidator = null;
    private $offerModel = null;
    private $offerContactModel = null;
    private $userModel = null;
    private $teamMemberModel = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        OfferContactValidator $offerContactValidator,
        OfferModel $offerModel,
        OfferContactModel $offerContactModel,
        UserModel $userModel,
        TeamMemberModel $teamMemberModel
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->offerContactValidator = $offerContactValidator;
        $this->offerModel = $offerModel;
        $this->offerContactModel = $offerContactModel;
        $this->userModel = $userModel;
        $this->teamMemberModel = $teamMemberModel;
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\OfferContact");
        $data = $request->json()->all();
        $this->offerContactValidator->validate("create", $data);
        $offer = $this->offerModel->findOrFail($data["offer_id"]);
        $this->authorize("update", $offer);
        /**
         * This section finds the relevant model, asserts the current user has
         * permission to assign them, and then checks the relevant model isn't
         * already assigned. We don't want duplicates.
         */
        if (array_key_exists("user_id", $data)) {
            $model = $this->userModel->findOrFail($data["user_id"]);
            $this->authorize("assign", $model);
            $existingModel = $this->offerContactModel
                ->where("offer_id", "=", $offer->id)
                ->where("user_id", "=", $data["user_id"])
                ->first();
        } else if (array_key_exists("team_member_id", $data)) {
            $model = $this->teamMemberModel->findOrFail($data["team_member_id"]);
            $this->authorize("update", $model);
            $existingModel = $this->offerContactModel
                ->where("offer_id", "=", $offer->id)
                ->where("team_member_id", "=", $data["team_member_id"])
                ->first();
        } else {
            throw new Exception();
        }
        if (!is_null($existingModel)) {
            throw new DuplicateOfferContactException();
        }
        $offerContact = $this->offerContactModel->newInstance($data);
        $offerContact->saveOrFail();
        $offerContact->refresh();
        return $this->jsonResponseFactory->success(new OfferContactResource($offerContact), 201);
    }

    public function readAllByOfferAction(Request $request, int $id): JsonResponse
    {
        $offer = $this->offerModel->findOrFail($id);
        $this->authorize("read", $offer);
        $offerContacts = $this->offerContactModel->where("offer_id", "=", $offer->id)->get();
        $resources = [];
        foreach ($offerContacts as $offerContact) {
            $resources[] = new OfferContactResource($offerContact);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $offerContact = $this->offerContactModel->findOrFail($id);
        $this->authorize("delete", $offerContact);
        $offerContact->delete();
        return $this->jsonResponseFactory->success((object) [], 200);
    }
}
