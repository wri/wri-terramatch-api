<?php

namespace App\Http\Controllers;

use App\Models\PitchContact as PitchContactModel;
use App\Models\OfferContact as OfferContactModel;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use App\Validators\TeamMemberValidator;
use App\Models\TeamMember as TeamMemberModel;
use App\Models\Upload as UploadModel;
use Illuminate\Auth\AuthManager;
use App\Services\FileService;
use App\Resources\TeamMemberResource;
use App\Models\Organisation as OrganisationModel;

class TeamMembersController extends Controller
{
    private $jsonResponseFactory = null;
    private $teamMemberValidator = null;
    private $teamMemberModel = null;
    private $uploadModel = null;
    private $authManager = null;
    private $fileService = null;
    private $organisationModel = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        TeamMemberValidator $teamMemberValidator,
        TeamMemberModel $teamMemberModel,
        UploadModel $uploadModel,
        AuthManager $authManager,
        FileService $fileService,
        OrganisationModel $organisationModel
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->teamMemberValidator = $teamMemberValidator;
        $this->teamMemberModel = $teamMemberModel;
        $this->uploadModel = $uploadModel;
        $this->authManager = $authManager;
        $this->fileService = $fileService;
        $this->organisationModel = $organisationModel;
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\TeamMember");
        $data = $request->json()->all();
        $this->teamMemberValidator->validate("create", $data);
        $me = $this->authManager->user();
        $data["avatar"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
            $data["avatar"], UploadModel::IMAGES, $me->id
        );
        $data["organisation_id"] = $me->organisation_id;
        $teamMember = $this->teamMemberModel->newInstance($data);
        $teamMember->saveOrFail();
        $teamMember->refresh();
        return $this->jsonResponseFactory->success(new TeamMemberResource($teamMember), 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $teamMember = $this->teamMemberModel->findOrFail($id);
        $this->authorize("read", $teamMember);
        return $this->jsonResponseFactory->success(new TeamMemberResource($teamMember), 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $teamMember = $this->teamMemberModel->findOrFail($id);
        $this->authorize("update", $teamMember);
        $data = $request->json()->all();
        $this->teamMemberValidator->validate("update", $data);
        $me = $this->authManager->user();
        if (array_key_exists("avatar", $data)) {
            $data["avatar"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
                $data["avatar"], UploadModel::IMAGES, $me->id
            );
        }
        $teamMember->fill($data);
        $teamMember->saveOrFail();
        return $this->jsonResponseFactory->success(new TeamMemberResource($teamMember), 200);
    }

    public function readAllByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $organisation = $this->organisationModel->findOrFail($id);
        $this->authorize("read", $organisation);
        $teamMembers = $this->teamMemberModel->where("organisation_id", "=", $organisation->id)->get();
        $resources = [];
        foreach ($teamMembers as $teamMember) {
            $resources[] = new TeamMemberResource($teamMember);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $teamMember = TeamMember::findOrFail($id);
        $this->authorize("delete", $teamMember);
        OfferContactModel::where("team_member_id", "=", $teamMember->id)->delete();
        PitchContactModel::where("team_member_id", "=", $teamMember->id)->delete();
        $teamMember->delete();
        return $this->jsonResponseFactory->success((object) [], 200);
    }
}
