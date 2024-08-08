<?php

namespace App\Policies;

use App\Models\CarbonCertification as CarbonCertificationModel;
use App\Models\DueSubmission;
use App\Models\Interest as InterestModel;
use App\Models\Matched as MatchModel;
use App\Models\Monitoring as MonitoringModel;
use App\Models\Offer as OfferModel;
use App\Models\OrganisationDocument as OrganisationDocumentModel;
use App\Models\Pitch as PitchModel;
use App\Models\PitchDocument as PitchDocumentModel;
use App\Models\RestorationMethodMetric as RestorationMethodMetricModel;
use App\Models\Site;
use App\Models\TreeSpecies as TreeSpeciesModel;
use App\Models\V2\User as UserModel;
use Exception;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Str;

abstract class Policy
{
    use HandlesAuthorization;

    protected function isGuest(?UserModel $user): bool
    {
        return ! ((bool) $user);
    }

    protected function isUser(?UserModel $user): bool
    {
        return ! $this->isGuest($user) && $this->isNewRoleUser($user);
    }

    protected function isAdmin(?UserModel $user): bool
    {
        return ! $this->isGuest($user) && $user->isAdmin;
    }

    protected function isNewRoleUser(?UserModel $user): bool
    {
        return $user->hasAnyRole(['project-developer', 'funder', 'government', 'project-manager']);
    }

    protected function isServiceAccount(?UserModel $user): bool
    {
        return ! $this->isGuest($user) && $user->hasRole('greenhouse-service-account');
    }

    protected function isOrphanedUser(?UserModel $user): bool
    {
        return $this->isUser($user) && ! ((bool) $user->organisation_id) && (count($user->all_my_organisations) == 0);
    }

    protected function isVerifiedUser(?UserModel $user): bool
    {
        return ($this->isUser($user) || $this->isNewRoleUser($user)) && (bool) $user->email_address_verified_at;
    }

    protected function isVerifiedAdmin(?UserModel $user): bool
    {
        return $this->isAdmin($user) && (bool) $user->email_address_verified_at;
    }

    protected function isTerrafundAdmin(?UserModel $user): bool
    {
        return ! $this->isGuest($user) && $user->hasRole('admin-terrafund');
    }

    protected function isFullUser(?UserModel $user): bool
    {
        return ! $this->isOrphanedUser($user) && $this->isVerifiedUser($user);
    }

    protected function hasApprovedOrganisation(?UserModel $user): bool
    {
        return (bool) $user->organisation->approved_version;
    }

    protected function isOwner(?UserModel $user, ?Object $model): bool
    {
        if ($this->isGuest($user) || ! ((bool) $model)) {
            return false;
        }
        switch (get_class($model)) {
            case UserModel::class:
            case \App\Models\Admin::class:
                return $user->id == $model->id;
            case \App\Models\Organisation::class:
                return $user->organisation_id == $model->id;
            case \App\Models\Upload::class:
            case \App\Models\Notification::class:
            case \App\Models\Device::class:
            case \App\Models\ElevatorVideo::class:
                return $user->id == $model->user_id;
            case \App\Models\EditHistory::class:
                return $user->id == $model->created_by_user_id;
            case \App\Models\TeamMember::class:
            case \App\Models\OrganisationVersion::class:
            case \App\Models\OrganisationDocument::class:
            case \App\Models\Offer::class:
            case \App\Models\Pitch::class:
            case 'App\\Models\\V2\\ProjectPitch\\ProjectPitch':
            case \App\Models\Interest::class:
                return $user->organisation_id == $model->organisation_id || $user->id == $model->created_by;
            case 'App\\Models\\V2\\Sites\\Site':
                return $user->organisation_id == $model->project->organisation_id;
            case 'App\\Models\\Draft':
                $userProgrammeIds = $user->programmes()->get()->pluck('id');
                $userSiteIds = Site::whereIn('programme_id', $userProgrammeIds)->pluck('id');
                $ppcDueSubmissionsForUser = DueSubmission::query()
                    ->where(function ($query) use ($userProgrammeIds) {
                        $query->where('due_submissionable_type', \App\Models\Programme::class)
                        ->whereIn('due_submissionable_id', $userProgrammeIds);
                    })
                    ->orWhere(function ($query) use ($userSiteIds) {
                        $query->where('due_submissionable_type', \App\Models\Site::class)
                        ->whereIn('due_submissionable_id', $userSiteIds);
                    })
                    ->pluck('id');

                return ($model->type !== 'organisation' && ($user->organisation_id == $model->organisation_id)) ||
                    $user->id == $model->created_by ||
                    $ppcDueSubmissionsForUser->contains($model->due_submission_id);
            case \App\Models\OrganisationDocumentVersion::class:
                $parent = OrganisationDocumentModel::findOrFail($model->organisation_document_id);

                return $this->isOwner($user, $parent);
            case \App\Models\OfferContact::class:
            case \App\Models\OfferDocument::class:
                $parent = OfferModel::findOrFail($model->offer_id);

                return $this->isOwner($user, $parent);
            case \App\Models\PitchVersion::class:
            case \App\Models\PitchContact::class:
            case \App\Models\PitchDocument::class:
            case \App\Models\CarbonCertification::class:
            case \App\Models\RestorationMethodMetric::class:
            case \App\Models\TreeSpecies::class:
                $parent = PitchModel::findOrFail($model->pitch_id);

                return $this->isOwner($user, $parent);
            case \App\Models\CarbonCertificationVersion::class:
                $parent = CarbonCertificationModel::findOrFail($model->carbon_certification_id);

                return $this->isOwner($user, $parent);
            case \App\Models\PitchDocumentVersion::class:
                $parent = PitchDocumentModel::findOrFail($model->pitch_document_id);

                return $this->isOwner($user, $parent);
            case \App\Models\RestorationMethodMetricVersion::class:
                $parent = RestorationMethodMetricModel::findOrFail($model->restoration_method_metric_id);

                return $this->isOwner($user, $parent);
            case \App\Models\TreeSpeciesVersion::class:
                $parent = TreeSpeciesModel::findOrFail($model->tree_species_id);

                return $this->isOwner($user, $parent);
            case \App\Models\Matched::class:
                $father = InterestModel::findOrFail($model->primary_interest_id);
                $mother = InterestModel::findOrFail($model->secondary_interest_id);
                $fatherIsParent = $this->isOwner($user, $father);
                $motherIsParent = $this->isOwner($user, $mother);

                return $fatherIsParent || $motherIsParent;
            case \App\Models\Monitoring::class:
                $parent = MatchModel::findOrFail($model->match_id);

                return $this->isOwner($user, $parent);
            case \App\Models\Target::class:
                $parent = MonitoringModel::findOrFail($model->monitoring_id);

                return $this->isOwner($user, $parent);
            case \App\Models\ProgressUpdate::class:
                $parent = MonitoringModel::findOrFail($model->monitoring_id);

                return $this->isOwner($user, $parent);
            case \App\Models\SatelliteMap::class:
                $parent = MonitoringModel::findOrFail($model->monitoring_id);

                return $this->isOwner($user, $parent);
            case \App\Models\Programme::class:
                return $user->programmes->contains($model->id);
            case \App\Models\Terrafund\TerrafundDueSubmission::class:
                return $user->terrafundProgrammes->contains($model->terrafund_programme_id);
            case \App\Models\Terrafund\TerrafundProgramme::class:
                return $user->terrafundProgrammes->contains($model->id);
            case \App\Models\Terrafund\TerrafundNursery::class:
                return $user->terrafundProgrammes->contains($model->terrafund_programme_id);
            case \App\Models\Terrafund\TerrafundSite::class:
                return $user->terrafundProgrammes->contains($model->terrafund_programme_id);
            case \App\Models\Terrafund\TerrafundNurserySubmission::class:
                return $user->terrafundProgrammes->contains($model->terrafundNursery->terrafund_programme_id);
            case \App\Models\Terrafund\TerrafundProgrammeSubmission::class:
                return $user->terrafundProgrammes->contains($model->terrafundProgramme->id);
            case \App\Models\Terrafund\TerrafundSiteSubmission::class:
                return $user->terrafundProgrammes->contains($model->terrafundSite->terrafund_programme_id);
            case \App\Models\ProgrammeTreeSpecies::class:
            case \App\Models\Site::class:
            case \App\Models\CsvImport::class:
            case \App\Models\Submission::class:
            case \App\Models\ProgrammeInvite::class:
            case \App\Models\MediaUpload::class:
                return $user->programmes->contains($model->programme_id);
            case \App\Models\SiteCsvImport::class:
            case \App\Models\SiteTreeSpecies::class:
            case \App\Models\SeedDetail::class:
            case \App\Models\Invasive::class:
                return $user->programmes->contains($model->site->programme_id);
            case 'App\\Models\\SubmissionCsvImport':
            case 'App\\Models\\SubmissionTreeSpecies':
            case \App\Models\SiteSubmissionDisturbance::class:
                return $user->programmes->contains($model->siteSubmission->site->programme_id);
            case \App\Models\SiteSubmission::class:
                return $user->programmes->contains($model->site->programme_id);
            case \App\Models\SubmissionMediaUpload::class:
                if ($model->submission_id !== null) {
                    return $user->programmes->contains($model->submission->programme_id);
                } else {
                    return $user->programmes->contains($model->siteSubmission->site->programme_id);
                }
                // no break
            case \App\Models\DueSubmission::class:
                if ($model->due_submissionable_type === \App\Models\Site::class) {
                    return $user->programmes->contains($model->due_submissionable->programme_id);
                } else {
                    return $user->programmes->contains($model->due_submissionable_id);
                }
                // no break
            default:
                throw new Exception();
        }
    }

    protected function isVisible(?UserModel $user, ?Object $model): bool
    {
        if ($this->isGuest($user) || ! ((bool) $model)) {
            return false;
        }
        switch (get_class($model)) {
            case \App\Models\Offer::class:
            case \App\Models\Pitch::class:
                $visibility = $model->visibility;

                break;
            case \App\Models\OfferContact::class:
            case \App\Models\OfferDocument::class:
                $parent = OfferModel::findOrFail($model->offer_id);
                $visibility = $parent->visibility;

                break;
            case \App\Models\PitchVersion::class:
            case \App\Models\PitchContact::class:
            case \App\Models\CarbonCertification::class:
            case \App\Models\PitchDocument::class:
            case \App\Models\RestorationMethodMetric::class:
            case \App\Models\TreeSpecies::class:
                $parent = PitchModel::findOrFail($model->pitch_id);
                $visibility = $parent->visibility;

                break;
            case \App\Models\CarbonCertificationVersion::class:
                $parent = CarbonCertificationModel::findOrFail($model->carbon_certification_id);
                $grandParent = PitchModel::findOrFail($parent->pitch_id);
                $visibility = $grandParent->visibility;

                break;
            case \App\Models\PitchDocumentVersion::class:
                $parent = PitchDocumentModel::findOrFail($model->pitch_document_id);
                $grandParent = PitchModel::findOrFail($parent->pitch_id);
                $visibility = $grandParent->visibility;

                break;
            case \App\Models\RestorationMethodMetricVersion::class:
                $parent = RestorationMethodMetricModel::findOrFail($model->restoration_method_metric_id);
                $grandParent = PitchModel::findOrFail($parent->pitch_id);
                $visibility = $grandParent->visibility;

                break;
            case \App\Models\TreeSpeciesVersion::class:
                $parent = TreeSpeciesModel::findOrFail($model->tree_species_id);
                $grandParent = PitchModel::findOrFail($parent->pitch_id);
                $visibility = $grandParent->visibility;

                break;
            default:
                throw new Exception();
        }

        return ! in_array($visibility, ['archived', 'finished']);
    }
}
