<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Jobs\NotifyProjectUpdatedJob;
use App\Models\Offer as OfferModel;
use App\Models\OfferDocument as OfferDocumentModel;
use App\Resources\OfferDocumentResource;
use App\Validators\OfferDocumentValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfferDocumentsController extends Controller
{
    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\OfferDocument");
        $data = $request->json()->all();
        OfferDocumentValidator::validate("CREATE", $data);
        $offer = OfferModel::findOrFail($data["offer_id"]);
        $this->authorize("update", $offer);
        $me = Auth::user();
        $data["document"] = UploadHelper::findByIdAndValidate(
            $data["document"], UploadHelper::IMAGES_FILES, $me->id
        );
        $offerDocument = new OfferDocumentModel($data);
        $offerDocument->saveOrFail();
        $offerDocument->refresh();
        NotifyProjectUpdatedJob::dispatch($offer);
        return JsonResponseHelper::success(new OfferDocumentResource($offerDocument), 201);
    }

    public function readAllByOfferAction(Request $request, int $id): JsonResponse
    {
        $offer = OfferModel::findOrFail($id);
        $this->authorize("read", $offer);
        $offerDocuments = OfferDocumentModel
            ::where("offer_id", "=", $offer->id)
            ->orderBy("name", "asc")
            ->get();
        $resources = [];
        foreach ($offerDocuments as $offerDocument) {
            $resources[] = new OfferDocumentResource($offerDocument);
        }
        return JsonResponseHelper::success($resources, 200);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $offerDocument = OfferDocumentModel::findOrFail($id);
        $this->authorize("read", $offerDocument);
        return JsonResponseHelper::success(new OfferDocumentResource($offerDocument), 200);
    }

    public function updateAction(Request $request, $id): JsonResponse
    {
        $offerDocument = OfferDocumentModel::findOrFail($id);
        $this->authorize("update", $offerDocument);
        $data = $request->json()->all();
        OfferDocumentValidator::validate("UPDATE", $data);
        $me = Auth::user();
        if (array_key_exists("document", $data)) {
            $data["document"] = UploadHelper::findByIdAndValidate(
                $data["document"], UploadHelper::IMAGES_FILES, $me->id
            );
        }
        $offerDocument->fill($data);
        $offerDocument->saveOrFail();
        $offer = OfferModel::findOrFail($offerDocument->offer_id);
        NotifyProjectUpdatedJob::dispatch($offer);
        return JsonResponseHelper::success(new OfferDocumentResource($offerDocument), 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $offerDocument = OfferDocumentModel::findOrFail($id);
        $this->authorize("delete", $offerDocument);
        $offerDocument->delete();
        return JsonResponseHelper::success((object) [], 200);
    }
}
