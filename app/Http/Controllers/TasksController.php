<?php

namespace App\Http\Controllers;

use App\Http\JsonResponseFactory;
use App\Services\VersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\CarbonCertificationVersion as CarbonCertificationVersionModel;
use App\Models\OrganisationDocumentVersion as OrganisationDocumentVersionModel;
use App\Models\OrganisationVersion as OrganisationVersionModel;
use App\Models\PitchDocumentVersion as PitchDocumentVersionModel;
use App\Models\PitchVersion as PitchVersionModel;
use App\Models\RestorationMethodMetricVersion as RestorationMethodMetricVersionModel;
use App\Models\TreeSpeciesVersion as TreeSpeciesVersionModel;
use App\Models\CarbonCertification as CarbonCertificationModel;
use App\Models\OrganisationDocument as OrganisationDocumentModel;
use App\Models\Organisation as OrganisationModel;
use App\Models\PitchDocument as PitchDocumentModel;
use App\Models\Pitch as PitchModel;
use App\Models\RestorationMethodMetric as RestorationMethodMetricModel;
use App\Models\TreeSpecies as TreeSpeciesModel;
use App\Resources\CarbonCertificationVersionResource;
use App\Resources\OrganisationDocumentVersionResource;
use App\Resources\OrganisationVersionResource;
use App\Resources\PitchDocumentVersionResource;
use App\Resources\PitchVersionResource;
use App\Resources\RestorationMethodMetricVersionResource;
use App\Resources\TreeSpeciesVersionResource;
use App\Resources\MatchResource;
use App\Services\MatchService;

class TasksController extends Controller
{
    protected $jsonResponseFactory = null;
    protected $carbonCertificationVersionModel = null;
    protected $organisationDocumentVersionModel = null;
    protected $organisationVersionModel = null;
    protected $pitchDocumentVersionModel = null;
    protected $pitchVersionModel = null;
    protected $restorationMethodMetricVersionModel = null;
    protected $treeSpeciesVersionModel = null;
    protected $carbonCertificationModel = null;
    protected $organisationDocumentModel = null;
    protected $organisationModel = null;
    protected $pitchDocumentModel = null;
    protected $pitchModel = null;
    protected $restorationMethodMetricModel = null;
    protected $treeSpeciesModel = null;
    protected $matchService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        CarbonCertificationVersionModel $carbonCertificationVersionModel,
        OrganisationDocumentVersionModel $organisationDocumentVersionModel,
        OrganisationVersionModel $organisationVersionModel,
        PitchDocumentVersionModel $pitchDocumentVersionModel,
        PitchVersionModel $pitchVersionModel,
        RestorationMethodMetricVersionModel $restorationMethodMetricVersionModel,
        TreeSpeciesVersionModel $treeSpeciesVersionModel,
        CarbonCertificationModel $carbonCertificationModel,
        OrganisationDocumentModel $organisationDocumentModel,
        OrganisationModel $organisationModel,
        PitchDocumentModel $pitchDocumentModel,
        PitchModel $pitchModel,
        RestorationMethodMetricModel $restorationMethodMetricModel,
        TreeSpeciesModel $treeSpeciesModel,
        MatchService $matchService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->carbonCertificationVersionModel = $carbonCertificationVersionModel;
        $this->organisationDocumentVersionModel = $organisationDocumentVersionModel;
        $this->organisationVersionModel = $organisationVersionModel;
        $this->pitchDocumentVersionModel = $pitchDocumentVersionModel;
        $this->pitchVersionModel = $pitchVersionModel;
        $this->restorationMethodMetricVersionModel = $restorationMethodMetricVersionModel;
        $this->treeSpeciesVersionModel = $treeSpeciesVersionModel;
        $this->carbonCertificationModel = $carbonCertificationModel;
        $this->organisationDocumentModel = $organisationDocumentModel;
        $this->organisationModel = $organisationModel;
        $this->pitchDocumentModel = $pitchDocumentModel;
        $this->pitchModel = $pitchModel;
        $this->restorationMethodMetricModel = $restorationMethodMetricModel;
        $this->treeSpeciesModel = $treeSpeciesModel;
        $this->matchService = $matchService;
    }

    public function readAllCarbonCertificationVersionsAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Task");
        $versionService = new VersionService($this->carbonCertificationModel, $this->carbonCertificationVersionModel);
        $parentsAndChildren = $versionService->findAllPendingChildren();
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new CarbonCertificationVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function readAllOrganisationDocumentVersionsAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Task");
        $versionService = new VersionService($this->organisationDocumentModel, $this->organisationDocumentVersionModel);
        $parentsAndChildren = $versionService->findAllPendingChildren();
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new OrganisationDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function readAllOrganisationVersionsAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Task");
        $versionService = new VersionService($this->organisationModel, $this->organisationVersionModel);
        $parentsAndChildren = $versionService->findAllPendingChildren();
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new OrganisationVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function readAllPitchDocumentVersionsAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Task");
        $versionService = new VersionService($this->pitchDocumentModel, $this->pitchDocumentVersionModel);
        $parentsAndChildren = $versionService->findAllPendingChildren();
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new PitchDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function readAllPitchVersionsAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Task");
        $versionService = new VersionService($this->pitchModel, $this->pitchVersionModel);
        $parentsAndChildren = $versionService->findAllPendingChildren();
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new PitchVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function readAllRestorationMethodMetricVersionsAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Task");
        $versionService = new VersionService($this->restorationMethodMetricModel, $this->restorationMethodMetricVersionModel);
        $parentsAndChildren = $versionService->findAllPendingChildren();
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new RestorationMethodMetricVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function readAllTreeSpeciesVersionsAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Task");
        $versionService = new VersionService($this->treeSpeciesModel, $this->treeSpeciesVersionModel);
        $parentsAndChildren = $versionService->findAllPendingChildren();
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new TreeSpeciesVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function readAllMatchesAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Task");
        $matches = $this->matchService->findMatches();
        $resources = [];
        foreach ($matches as $match) {
            $offerContacts = $this->matchService->findOfferContacts($match->offer_id);
            $pitchContacts = $this->matchService->findPitchContacts($match->pitch_id);
            $resources[] = new MatchResource($match, $offerContacts, $pitchContacts);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }
}
