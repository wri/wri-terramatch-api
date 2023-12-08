<?php

namespace App\Http\Controllers;

use App\Exceptions\MonitoringExistsException;
use App\Helpers\JsonResponseHelper;
use App\Helpers\MonitoringHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\MostRecentActionOfferRequest;
use App\Jobs\CreateFilterRecordJob;
use App\Jobs\NotifyProjectUpdatedJob;
use App\Models\Offer as OfferModel;
use App\Models\Organisation as OrganisationModel;
use App\Resources\OfferResource;
use App\Validators\OfferValidator;
use DateTime;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class OffersController extends Controller
{
    public function createAction(Request $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Offer::class);
        $data = $request->json()->all();
        OfferValidator::validate('CREATE', $data);
        $me = Auth::user();
        $data['cover_photo'] = UploadHelper::findByIdAndValidate(
            $data['cover_photo'],
            UploadHelper::IMAGES,
            $me->id
        );
        $data['video'] = UploadHelper::findByIdAndValidate(
            $data['video'],
            UploadHelper::VIDEOS,
            $me->id
        );
        UploadHelper::assertUnique($data['cover_photo'], $data['video']);
        $extra = [
            'organisation_id' => $me->organisation_id,
            'visibility_updated_at' => new DateTime('now', new DateTimeZone('UTC')),
        ];

        $offer = OfferModel::create(array_merge($data, $extra));

        return JsonResponseHelper::success(new OfferResource($offer), 201);
    }

    public function readAllByOrganisationAction(OrganisationModel $organisation): JsonResponse
    {
        $this->authorize('read', $organisation);
        $offers = OfferModel::where('organisation_id', '=', $organisation->id)
            ->whereNotIn('visibility', ['archived', 'finished'])
            ->orderBy('name')
            ->get();
        $resources = [];
        foreach ($offers as $offer) {
            $resources[] = new OfferResource($offer);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAction(OfferModel $offer): JsonResponse
    {
        $this->authorize('read', $offer);

        return JsonResponseHelper::success(new OfferResource($offer), 200);
    }

    public function updateAction(OfferModel $offer, Request $request): JsonResponse
    {
        $this->authorize('update', $offer);
        $data = $request->json()->all();
        OfferValidator::validate('UPDATE', $data);
        $me = Auth::user();
        if (array_key_exists('cover_photo', $data)) {
            $data['cover_photo'] = UploadHelper::findByIdAndValidate(
                $data['cover_photo'],
                UploadHelper::IMAGES,
                $me->id
            );
        }
        if (array_key_exists('video', $data)) {
            $data['video'] = UploadHelper::findByIdAndValidate(
                $data['video'],
                UploadHelper::VIDEOS,
                $me->id
            );
        }
        UploadHelper::assertUnique(@$data['cover_photo'], @$data['video']);

        $offer->update($data);

        NotifyProjectUpdatedJob::dispatch($offer);

        return JsonResponseHelper::success(new OfferResource($offer), 200);
    }

    public function searchAction(Request $request): JsonResponse
    {
        $searchService = App::make(\App\Services\Search\SearchService::class);
        $this->authorize('search', \App\Models\Offer::class);
        $conditions = $searchService->parse($request);
        $offers = OfferModel::search($conditions, Auth::user()->organisation_id)->get();
        CreateFilterRecordJob::dispatch(Auth::user(), 'offers', $conditions);
        $resources = [];
        foreach ($offers as $offer) {
            $resources[] = new OfferResource($offer, true);
        }
        $meta = $searchService->summarise('Offer', $resources, $conditions);

        return JsonResponseHelper::success($resources, 200, $meta);
    }

    public function updateVisibilityAction(OfferModel $offer, Request $request): JsonResponse
    {
        $this->authorize('updateVisibility', $offer);
        $data = $request->json()->all();
        OfferValidator::validate('UPDATE_VISIBILITY', $data);
        if (! MonitoringHelper::isNewVisibilityValid($offer, $data['visibility'])) {
            throw new MonitoringExistsException();
        }

        $offer->update([
            'visibility' => $data['visibility'],
            'visibility_updated_at' => new DateTime('now', new DateTimeZone('UTC')),
        ]);
        MonitoringHelper::progressRelatedMonitoringStages($offer, $data['visibility']);

        return JsonResponseHelper::success(new OfferResource($offer), 200);
    }

    public function inspectByOrganisationAction(OrganisationModel $organisation): JsonResponse
    {
        $this->authorize('inspect', $organisation);
        $offers = OfferModel::where('organisation_id', '=', $organisation->id)
            ->orderBy('name')
            ->get();
        $resources = [];
        foreach ($offers as $offer) {
            $resources[] = new OfferResource($offer);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function mostRecentAction(MostRecentActionOfferRequest $request): JsonResponse
    {
        $this->authorize('search', \App\Models\Offer::class);
        $limit = (int) $request->get('limit', 10);
        $offers = OfferModel::orderByDesc('created_at')
            ->limit($limit)
            ->get();
        $resources = $offers->map(function ($offer) {
            return new OfferResource($offer);
        });

        return JsonResponseHelper::success($resources, 200);
    }
}
