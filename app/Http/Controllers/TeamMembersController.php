<?php

namespace App\Http\Controllers;

use App\Exceptions\UsedTeamMemberException;
use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Models\OfferContact as OfferContactModel;
use App\Models\Organisation as OrganisationModel;
use App\Models\PitchContact as PitchContactModel;
use App\Models\TeamMember as TeamMemberModel;
use App\Resources\MaskedTeamMemberResource;
use App\Resources\TeamMemberResource;
use App\Validators\TeamMemberValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class TeamMembersController extends Controller
{
    public function createAction(Request $request): JsonResponse
    {
        $this->authorize('create', \App\Models\TeamMember::class);
        $data = $request->json()->all();
        TeamMemberValidator::validate('CREATE', $data);
        $me = Auth::user();
        $data['avatar'] = UploadHelper::findByIdAndValidate(
            $data['avatar'],
            UploadHelper::IMAGES,
            $me->id
        );
        $data['organisation_id'] = $me->organisation_id;
        $teamMember = new TeamMemberModel($data);
        $teamMember->saveOrFail();
        $teamMember->refresh();

        return JsonResponseHelper::success(new TeamMemberResource($teamMember), 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $teamMember = TeamMemberModel::findOrFail($id);
        $this->authorize('read', $teamMember);

        return JsonResponseHelper::success(new TeamMemberResource($teamMember), 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $teamMember = TeamMemberModel::findOrFail($id);
        $this->authorize('update', $teamMember);
        $data = $request->json()->all();
        TeamMemberValidator::validate('UPDATE', $data);
        $me = Auth::user();
        if (array_key_exists('avatar', $data)) {
            $data['avatar'] = UploadHelper::findByIdAndValidate(
                $data['avatar'],
                UploadHelper::IMAGES,
                $me->id
            );
        }
        $teamMember->fill($data);
        $teamMember->saveOrFail();

        return JsonResponseHelper::success(new TeamMemberResource($teamMember), 200);
    }

    public function readAllByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $organisation = OrganisationModel::findOrFail($id);
        $this->authorize('read', $organisation);
        $teamMembers = TeamMemberModel::where('organisation_id', '=', $organisation->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        $resources = [];
        foreach ($teamMembers as $teamMember) {
            $resources[] = new MaskedTeamMemberResource($teamMember);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function inspectByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $organisation = OrganisationModel::findOrFail($id);
        $this->authorize('inspect', $organisation);
        $teamMembers = TeamMemberModel::where('organisation_id', '=', $organisation->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        $resources = [];
        foreach ($teamMembers as $teamMember) {
            $resources[] = new TeamMemberResource($teamMember);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $teamMember = TeamMemberModel::findOrFail($id);
        $this->authorize('delete', $teamMember);
        $pitchContacts = PitchContactModel::where('team_member_id', '=', $teamMember->id)->count();
        $offerContacts = OfferContactModel::where('team_member_id', '=', $teamMember->id)->count();
        if ($pitchContacts > 0 || $offerContacts > 0) {
            throw new UsedTeamMemberException();
        }
        if (! is_null($teamMember->avatar)) {
            $fileService = App::make(\App\Services\FileService::class);
            $fileService->delete($teamMember->avatar);
        }
        $teamMember->delete();

        return JsonResponseHelper::success((object) [], 200);
    }
}
