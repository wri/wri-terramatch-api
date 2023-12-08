<?php

namespace App\Http\Controllers;

use App\Exceptions\DuplicateOfferContactException;
use App\Exceptions\FinalOfferContactException;
use App\Exceptions\InvalidOfferContactException;
use App\Helpers\JsonResponseHelper;
use App\Http\Requests\StoreOfferContactsRequest;
use App\Models\Offer as OfferModel;
use App\Models\OfferContact as OfferContactModel;
use App\Models\TeamMember as TeamMemberModel;
use App\Models\User as UserModel;
use App\Resources\OfferContactResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class OfferContactsController extends Controller
{
    public function createAction(StoreOfferContactsRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\OfferContact::class);
        $data = $request->json()->all();
        $offer = OfferModel::findOrFail($data['offer_id']);
        $this->authorize('update', $offer);
        /**
         * This section finds the relevant model, asserts the current user has
         * permission to assign them, and then checks the relevant model isn't
         * already assigned. We don't want duplicates.
         */
        if (array_key_exists('user_id', $data) && array_key_exists('team_member_id', $data)) {
            throw new InvalidOfferContactException();
        } elseif (array_key_exists('user_id', $data)) {
            $model = UserModel::findOrFail($data['user_id']);
            $this->authorize('assign', $model);
            $existingModel = OfferContactModel::where('offer_id', '=', $offer->id)
                ->where('user_id', '=', $data['user_id'])
                ->first();
        } elseif (array_key_exists('team_member_id', $data)) {
            $model = TeamMemberModel::findOrFail($data['team_member_id']);
            $this->authorize('update', $model);
            $existingModel = OfferContactModel::where('offer_id', '=', $offer->id)
                ->where('team_member_id', '=', $data['team_member_id'])
                ->first();
        } else {
            throw new InvalidOfferContactException();
        }
        if (! is_null($existingModel)) {
            throw new DuplicateOfferContactException();
        }

        $offerContact = OfferContactModel::create($data);

        return JsonResponseHelper::success(new OfferContactResource($offerContact), 201);
    }

    public function readAllByOfferAction(OfferModel $offer): JsonResponse
    {
        $this->authorize('read', $offer);
        $offerContacts = OfferContactModel::where('offer_id', '=', $offer->id)->get();
        $resources = [];
        foreach ($offerContacts as $offerContact) {
            $resources[] = new OfferContactResource($offerContact);
        }
        Arr::sort($resources, function ($resource) {
            return $resource->last_name . ' ' . $resource->first_name;
        });

        return JsonResponseHelper::success($resources, 200);
    }

    public function deleteAction(OfferContactModel $offerContact): JsonResponse
    {
        $this->authorize('delete', $offerContact);
        $count = OfferContactModel::where('offer_id', '=', $offerContact->offer_id)->count();
        if ($count <= 1) {
            throw new FinalOfferContactException();
        }
        $offerContact->delete();

        return JsonResponseHelper::success((object) [], 200);
    }
}
