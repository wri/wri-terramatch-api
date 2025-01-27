<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidJsonPatchException;
use App\Exceptions\InvalidUploadTypeException;
use App\Exceptions\MismatchingDraftTypeException;
use App\Exceptions\NoMatchingDraftsException;
use App\Exceptions\UploadNotFoundException;
use App\Helpers\DraftHelper;
use App\Helpers\JsonPatchHelper;
use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Jobs\PublishDraftOfferJob;
use App\Jobs\PublishDraftOrganisationJob;
use App\Jobs\PublishDraftPitchJob;
use App\Jobs\PublishDraftProgrammeJob;
use App\Jobs\PublishDraftProgrammeSubmissionJob;
use App\Jobs\PublishDraftSiteJob;
use App\Jobs\PublishDraftSiteSubmissionJob;
use App\Jobs\PublishDraftTerrafundNurseryJob;
use App\Jobs\PublishDraftTerrafundNurserySubmissionJob;
use App\Jobs\PublishDraftTerrafundProgrammeJob;
use App\Jobs\PublishDraftTerrafundProgrammeSubmissionJob;
use App\Jobs\PublishDraftTerrafundSiteJob;
use App\Jobs\PublishDraftTerrafundSiteSubmissionJob;
use App\Models\Draft as DraftModel;
use App\Models\DueSubmission;
use App\Models\Programme;
use App\Models\Site;
use App\Resources\DraftResource;
use App\Validators\DraftValidator;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Pluralizer;
use Swaggest\JsonDiff\JsonPatch;
use Throwable;

class DraftsController extends Controller
{
    public function createAction(Request $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Draft::class);
        $data = $request->json()->all();
        DraftValidator::validate('CREATE', $data);
        $me = Auth::user();
        $draft = new DraftModel();
        $draft->name = $data['name'];
        $draft->type = $data['type'];
        $draft->is_from_mobile = isset($data['is_from_mobile']) ? $data['is_from_mobile'] : false;
        $draft->data = json_encode(DraftHelper::drafting($draft->type)::BLUEPRINT);


        if ($draft->type !== 'organisation') {
            $draft->organisation_id = $me->organisation_id;
        }
        $draft->created_by = $me->id;
        $draft->saveOrFail();
        $draft->refresh();
        if (isset($data['due_submission_id'])) {
            $dueSubmission = DueSubmission::find($data['due_submission_id']);
            $this->authorize('assignToDraft', $dueSubmission);
            $draft->due_submission_id = $data['due_submission_id'];
            $draft->saveOrFail();
        }
        $resource = new DraftResource($draft);

        return JsonResponseHelper::success($resource, 201);
    }

    public function readAction(DraftModel $draft): JsonResponse
    {
        $this->authorize('read', $draft);
        $resource = new DraftResource($draft);

        return JsonResponseHelper::success($resource, 200);
    }

    public function readAllByTypeAction(Request $request, String $type): JsonResponse
    {
        $this->authorize('readAll', \App\Models\Draft::class);
        $me = Auth::user();
        $userProgrammeIds = $me->programmes->pluck('id');
        $userSiteIds = Site::whereIn('programme_id', $userProgrammeIds)->pluck('id');
        $ppcDueSubmissionsForUser = DueSubmission::query()
        ->where(function ($query) use ($userProgrammeIds) {
            $query->where('due_submissionable_type', Programme::class)
            ->whereIn('due_submissionable_id', $userProgrammeIds);
        })
        ->orWhere(function ($query) use ($userSiteIds) {
            $query->where('due_submissionable_type', Site::class)
            ->whereIn('due_submissionable_id', $userSiteIds);
        })
        ->pluck('id');
        $drafts = DraftModel::where('type', '=', Pluralizer::singular($type))
            ->where(function ($query) use ($me) {
                $query->whereNotNull('organisation_id')
                ->where('organisation_id', '=', $me->organisation_id)
                ->orWhere('created_by', $me->id);
            })
            ->orWhere(function ($query) use ($ppcDueSubmissionsForUser, $type) {
                $query->where('type', '=', Pluralizer::singular($type))
                    ->whereIn('due_submission_id', $ppcDueSubmissionsForUser);
            })
            ->get();

        $resources = [];
        foreach ($drafts as $draft) {
            $resources[] = new DraftResource($draft);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function updateAction(DraftModel $draft, Request $request): JsonResponse
    {
        $this->authorize('update', $draft);
        $me = Auth::user();
        $data = json_decode($draft->data);
        /**
         * This section manually extracts the JSON Patch from the body of the
         * request. If we use Laravel's helper it will come back as an
         * associative array, which is not valid JSON Patch.
         */
        $patch = json_decode($request->getContent());
        if (! is_array($patch)) {
            throw new InvalidJsonPatchException();
        }

        /**
         * This section attempts to reorder the remove ops. If for any reason
         * the method errors (and it might as we haven't validated the JSON
         * Patch yet), we should attempt to continue as normal, hence the empty
         * catch.
         */
        try {
            $patch = JsonPatchHelper::reorderRemoveOps($patch);
        } catch (Throwable $thrown) {
        }

        try {
            JsonPatch::import($patch)->apply($data);
        } catch (Exception $exception) {
            throw new InvalidJsonPatchException();
        }
        /**
         * This section converts $data from an object into an associative
         * array. Laravel's validators only works on associative arrays as
         * that's what PHP would normally return in $_POST.
         */
        $arrayData = json_decode(json_encode($data), true);
        DraftValidator::validate('UPDATE_DATA_' . strtoupper($draft->type), $arrayData);
        /**
         * This section extracts the uploads, asserts they are all unique, and
         * then iterates over them. It is asserted that every upload belongs to
         * the same organisation as the current user may not be the user who
         * originally created the draft. It is also asserted that every every
         * upload is not a TIFF since those are only used by admins when creating
         * satellite maps.
         */
        $uploads = DraftHelper::drafting($draft->type)::extractUploads($data);
        UploadHelper::assertUnique(...$uploads);
        foreach ($uploads as $upload) {
            if ($upload->user->organisation_id != $me->organisation_id) {
                throw new UploadNotFoundException();
            } elseif (pathinfo($upload->location, PATHINFO_EXTENSION) == 'tiff') {
                throw new InvalidUploadTypeException();
            }
        }

        $draft->data = json_encode($data);
        $draft->updated_by = $me->id;
        $draft->saveOrFail();
        $draft->refresh();
        $resource = new DraftResource($draft);

        return JsonResponseHelper::success($resource, 200);
    }

    public function deleteAction(DraftModel $draft): JsonResponse
    {
        $this->authorize('delete', $draft);
        $draft->delete();

        return JsonResponseHelper::success((object) [], 200);
    }

    public function publishAction(DraftModel $draft): JsonResponse
    {
        $this->authorize('publish', $draft);
        $me = Auth::user();
        switch ($draft->type) {
            case 'offer':
                $id = PublishDraftOfferJob::dispatchSync($me, $draft);

                break;
            case 'pitch':
                $id = PublishDraftPitchJob::dispatchSync($me, $draft);

                break;
            case 'programme':
                $id = PublishDraftProgrammeJob::dispatchSync($me, $draft);

                break;
            case 'site':
                $id = PublishDraftSiteJob::dispatchSync($me, $draft);

                break;
            case 'site_submission':
                $id = PublishDraftSiteSubmissionJob::dispatchSync($me, $draft);

                break;
            case 'programme_submission':
                $id = PublishDraftProgrammeSubmissionJob::dispatchSync($me, $draft);

                break;
            case 'terrafund_programme':
                $id = PublishDraftTerrafundProgrammeJob::dispatchSync($me, $draft);

                break;
            case 'terrafund_nursery':
                $id = PublishDraftTerrafundNurseryJob::dispatchSync($me, $draft);

                break;
            case 'terrafund_site':
                $id = PublishDraftTerrafundSiteJob::dispatchSync($me, $draft);

                break;
            case 'organisation':
                $id = PublishDraftOrganisationJob::dispatchSync($me, $draft);

                break;
            case 'terrafund_nursery_submission':
                $id = PublishDraftTerrafundNurserySubmissionJob::dispatchSync($me, $draft);

                break;
            case 'terrafund_site_submission':
                $id = PublishDraftTerrafundSiteSubmissionJob::dispatchSync($me, $draft);

                break;
            case 'terrafund_programme_submission':
                $id = PublishDraftTerrafundProgrammeSubmissionJob::dispatchSync($me, $draft);

                break;
        }
        $key = $draft->type . '_id';

        return JsonResponseHelper::success((object) [$key => $id], 201);
    }

    public function mergeAction(Request $request): JsonResponse
    {
        $data = $request->json()->all();
        DraftValidator::validate('MERGE', $data);

        $drafts = DraftModel::whereIn('id', $data['draft_ids'])->get();
        if ($drafts->count() === 0) {
            throw new NoMatchingDraftsException();
        }
        $baseDraft = $drafts->first();
        if ($baseDraft->type !== $data['type']) {
            throw new MismatchingDraftTypeException();
        }
        $drafts->shift();
        $this->authorize('update', $baseDraft);
        $baseDraftData = json_decode($baseDraft->data, true);
        $type = $data['type'];

        switch ($type) {
            case 'site_submission':
                foreach ($drafts as $draft) {
                    $this->authorize('update', $draft);
                    if ($draft->type !== $type) {
                        throw new MismatchingDraftTypeException();
                    }
                    $data = json_decode($draft->data, true);
                    $baseDraftData['site_tree_species'] = array_merge($baseDraftData['site_tree_species'], $data['site_tree_species']);
                    $baseDraftData['direct_seeding']['kg_by_species'] = array_merge($baseDraftData['direct_seeding']['kg_by_species'], $data['direct_seeding']['kg_by_species']);
                    $baseDraftData['direct_seeding']['direct_seeding_kg'] += $data['direct_seeding']['direct_seeding_kg'];
                    if (! is_null($baseDraftData['disturbance_information']) && ! is_null($data['disturbance_information'])) {
                        $baseDraftData['disturbance_information'] = $baseDraftData['disturbance_information'] . ' ' . $data['disturbance_information'];
                    } elseif (! is_null($data['disturbance_information'])) {
                        $baseDraftData['disturbance_information'] = $data['disturbance_information'];
                    }
                    $baseDraftData['disturbances'] = array_merge($baseDraftData['disturbances'], $data['disturbances']);
                    $baseDraftData['media'] = array_merge($baseDraftData['media'], $data['media']);
                    $draft->delete();
                }

                break;
            case 'programme_submission':
                foreach ($drafts as $draft) {
                    $this->authorize('update', $draft);
                    if ($draft->type !== $type) {
                        throw new MismatchingDraftTypeException();
                    }
                    $data = json_decode($draft->data, true);
                    $baseDraftData['programme_tree_species'] = array_merge($baseDraftData['programme_tree_species'], $data['programme_tree_species']);
                    $baseDraftData['narratives']['technical_narrative'] = $baseDraftData['narratives']['technical_narrative'] . ' ' . $data['narratives']['technical_narrative'];
                    $baseDraftData['narratives']['public_narrative'] = $baseDraftData['narratives']['public_narrative'] . ' ' . $data['narratives']['public_narrative'];
                    $baseDraftData['media'] = array_merge($baseDraftData['media'], $data['media']);
                    $draft->delete();
                }

                break;
            case 'terrafund_programme_submission':
                foreach ($drafts as $draft) {
                    $this->authorize('update', $draft);
                    if ($draft->type !== $type) {
                        throw new MismatchingDraftTypeException();
                    }
                    $data = json_decode($draft->data, true);
                    $baseDraftData['terrafund_programme_submission']['shared_drive_link'] = $baseDraftData['terrafund_programme_submission']['shared_drive_link'] . ' ' . $data['terrafund_programme_submission']['shared_drive_link'];
                    $baseDraftData['terrafund_programme_submission']['landscape_community_contribution'] = $baseDraftData['terrafund_programme_submission']['landscape_community_contribution'] . ' ' . $data['terrafund_programme_submission']['landscape_community_contribution'];
                    $baseDraftData['terrafund_programme_submission']['top_three_successes'] = $baseDraftData['terrafund_programme_submission']['top_three_successes'] . ' ' . $data['terrafund_programme_submission']['top_three_successes'];
                    $baseDraftData['terrafund_programme_submission']['challenges_and_lessons'] = $baseDraftData['terrafund_programme_submission']['challenges_and_lessons'] . ' ' . $data['terrafund_programme_submission']['challenges_and_lessons'];
                    $baseDraftData['terrafund_programme_submission']['maintenance_and_monitoring_activities'] = $baseDraftData['terrafund_programme_submission']['maintenance_and_monitoring_activities'] . ' ' . $data['terrafund_programme_submission']['maintenance_and_monitoring_activities'];
                    $baseDraftData['terrafund_programme_submission']['significant_change'] = $baseDraftData['terrafund_programme_submission']['significant_change'] . ' ' . $data['terrafund_programme_submission']['significant_change'];
                    $baseDraftData['terrafund_programme_submission']['survival_calculation'] = $baseDraftData['terrafund_programme_submission']['survival_calculation'] . ' ' . $data['terrafund_programme_submission']['survival_calculation'];
                    $baseDraftData['terrafund_programme_submission']['survival_comparison'] = $baseDraftData['terrafund_programme_submission']['survival_comparison'] . ' ' . $data['terrafund_programme_submission']['survival_comparison'];
                    $baseDraftData['photos'] = array_merge($baseDraftData['photos'], $data['photos']);
                    $draft->delete();
                }

                break;
            case 'terrafund_site_submission':
                foreach ($drafts as $draft) {
                    $this->authorize('update', $draft);
                    if ($draft->type !== $type) {
                        throw new MismatchingDraftTypeException();
                    }
                    $data = json_decode($draft->data, true);
                    $baseDraftData['terrafund_site_submission']['shared_drive_link'] = $baseDraftData['terrafund_site_submission']['shared_drive_link'] . ' ' . $data['terrafund_site_submission']['shared_drive_link'];
                    $baseDraftData['photos'] = array_merge($baseDraftData['photos'], $data['photos']);
                    $baseDraftData['tree_species'] = array_merge($baseDraftData['photos'], $data['photos']);
                    $baseDraftData['non_tree_species'] = array_merge($baseDraftData['photos'], $data['photos']);
                    $baseDraftData['disturbances'] = array_merge($baseDraftData['photos'], $data['photos']);
                    $draft->delete();
                }

                break;
            case 'terrafund_nursery_submission':
                foreach ($drafts as $draft) {
                    $this->authorize('update', $draft);
                    if ($draft->type !== $type) {
                        throw new MismatchingDraftTypeException();
                    }
                    $data = json_decode($draft->data, true);
                    $baseDraftData['terrafund_nursery_submission']['interesting_facts'] = $baseDraftData['terrafund_nursery_submission']['interesting_facts'] . ' ' . $data['terrafund_nursery_submission']['interesting_facts'];
                    $baseDraftData['terrafund_nursery_submission']['site_prep'] = $baseDraftData['terrafund_nursery_submission']['site_prep'] . ' ' . $data['terrafund_nursery_submission']['site_prep'];
                    $draft->delete();
                }

                break;
        }

        $baseDraft->data = json_encode($baseDraftData);
        $baseDraft->is_merged = true;
        $baseDraft->save();
        $resource = new DraftResource($baseDraft);

        return JsonResponseHelper::success($resource, 200);
    }
}
