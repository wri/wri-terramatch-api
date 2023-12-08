<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\StoreOfferDocumentsRequest;
use App\Http\Requests\UpdateOfferDocumentsRequest;
use App\Jobs\NotifyProjectUpdatedJob;
use App\Models\Offer as OfferModel;
use App\Models\OfferDocument as OfferDocumentModel;
use App\Resources\OfferDocumentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OfferDocumentsController extends Controller
{
    public function createAction(StoreOfferDocumentsRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\OfferDocument::class);
        $data = $request->json()->all();
        $offer = OfferModel::findOrFail($data['offer_id']);
        $this->authorize('update', $offer);
        $me = Auth::user();
        $data['document'] = UploadHelper::findByIdAndValidate(
            $data['document'],
            UploadHelper::IMAGES_FILES,
            $me->id
        );

        $offerDocument = OfferDocumentModel::create($data);

        NotifyProjectUpdatedJob::dispatch($offer);

        return JsonResponseHelper::success(new OfferDocumentResource($offerDocument), 201);
    }

    public function readAllByOfferAction(OfferModel $offer): JsonResponse
    {
        $this->authorize('read', $offer);
        $offerDocuments = OfferDocumentModel::where('offer_id', '=', $offer->id)
            ->orderBy('name')
            ->get();
        $resources = [];
        foreach ($offerDocuments as $offerDocument) {
            $resources[] = new OfferDocumentResource($offerDocument);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAction(OfferDocumentModel $offerDocument): JsonResponse
    {
        $this->authorize('read', $offerDocument);

        return JsonResponseHelper::success(new OfferDocumentResource($offerDocument), 200);
    }

    public function updateAction(OfferDocumentModel $offerDocument, UpdateOfferDocumentsRequest $request): JsonResponse
    {
        $this->authorize('update', $offerDocument);
        $data = $request->json()->all();
        $me = Auth::user();
        if (array_key_exists('document', $data)) {
            $data['document'] = UploadHelper::findByIdAndValidate(
                $data['document'],
                UploadHelper::IMAGES_FILES,
                $me->id
            );
        }

        $offerDocument->update($data);

        $offer = OfferModel::findOrFail($offerDocument->offer_id);
        NotifyProjectUpdatedJob::dispatch($offer);

        return JsonResponseHelper::success(new OfferDocumentResource($offerDocument), 200);
    }

    public function deleteAction(OfferDocumentModel $offerDocument): JsonResponse
    {
        $this->authorize('delete', $offerDocument);
        $offerDocument->delete();

        return JsonResponseHelper::success((object) [], 200);
    }
}
