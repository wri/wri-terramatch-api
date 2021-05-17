<?php

namespace App\Policies;

use App\Models\User as UserModel;
use Exception;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\OrganisationDocument as OrganisationDocumentModel;
use App\Models\Offer as OfferModel;
use App\Models\Pitch as PitchModel;
use App\Models\CarbonCertification as CarbonCertificationModel;
use App\Models\PitchDocument as PitchDocumentModel;
use App\Models\RestorationMethodMetric as RestorationMethodMetricModel;
use App\Models\TreeSpecies as TreeSpeciesModel;
use App\Models\Interest as InterestModel;
use App\Models\Match as MatchModel;
use App\Models\Monitoring as MonitoringModel;

abstract class Policy
{
    use HandlesAuthorization;

    protected function isGuest(?UserModel $user): bool
    {
        return !((bool) $user);
    }

    protected function isUser(?UserModel $user): bool
    {
        return !$this->isGuest($user) && $user->role == "user";
    }

    protected function isAdmin(?UserModel $user): bool
    {
        return !$this->isGuest($user) && $user->role == "admin";
    }

    protected function isOrphanedUser(?UserModel $user): bool
    {
        return $this->isUser($user) && !((bool) $user->organisation_id);
    }

    protected function isVerifiedUser(?UserModel $user): bool
    {
        return $this->isUser($user) && (bool) $user->email_address_verified_at;
    }

    protected function isVerifiedAdmin(?UserModel $user): bool
    {
        return $this->isAdmin($user) && (bool) $user->email_address_verified_at;
    }

    protected function isFullUser(?UserModel $user): bool
    {
        return !$this->isOrphanedUser($user) && $this->isVerifiedUser($user);
    }

    protected function hasApprovedOrganisation(?UserModel $user): bool
    {
        return (bool) $user->organisation->approved_version;
    }

    protected function isOwner(?UserModel $user, ?Object $model): bool
    {
        if ($this->isGuest($user) || !((bool) $model)) {
            return false;
        }
        switch (get_class($model)) {
            case "App\\Models\\User":
            case "App\\Models\\Admin":
                return $user->id == $model->id;
            case "App\\Models\\Organisation":
                return $user->organisation_id == $model->id;
            case "App\\Models\\Upload":
            case "App\\Models\\Notification":
            case "App\\Models\\Device":
            case "App\\Models\\ElevatorVideo":
                return $user->id == $model->user_id;
            case "App\\Models\\TeamMember":
            case "App\\Models\\OrganisationVersion":
            case "App\\Models\\OrganisationDocument":
            case "App\\Models\\Offer":
            case "App\\Models\\Pitch":
            case "App\\Models\\Interest":
            case "App\\Models\\Draft":
                return $user->organisation_id == $model->organisation_id;
            case "App\\Models\\OrganisationDocumentVersion":
                $parent = OrganisationDocumentModel::findOrFail($model->organisation_document_id);
                return $this->isOwner($user, $parent);
            case "App\\Models\\OfferContact":
            case "App\\Models\\OfferDocument":
                $parent = OfferModel::findOrFail($model->offer_id);
                return $this->isOwner($user, $parent);
            case "App\\Models\\PitchVersion":
            case "App\\Models\\PitchContact":
            case "App\\Models\\PitchDocument":
            case "App\\Models\\CarbonCertification":
            case "App\\Models\\RestorationMethodMetric":
            case "App\\Models\\TreeSpecies":
                $parent = PitchModel::findOrFail($model->pitch_id);
                return $this->isOwner($user, $parent);
            case "App\\Models\\CarbonCertificationVersion":
                $parent = CarbonCertificationModel::findOrFail($model->carbon_certification_id);
                return $this->isOwner($user, $parent);
            case "App\\Models\\PitchDocumentVersion":
                $parent = PitchDocumentModel::findOrFail($model->pitch_document_id);
                return $this->isOwner($user, $parent);
            case "App\\Models\\RestorationMethodMetricVersion":
                $parent = RestorationMethodMetricModel::findOrFail($model->restoration_method_metric_id);
                return $this->isOwner($user, $parent);
            case "App\\Models\\TreeSpeciesVersion":
                $parent = TreeSpeciesModel::findOrFail($model->tree_species_id);
                return $this->isOwner($user, $parent);
            case "App\\Models\\Match":
                $father = InterestModel::findOrFail($model->primary_interest_id);
                $mother = InterestModel::findOrFail($model->secondary_interest_id);
                $fatherIsParent = $this->isOwner($user, $father);
                $motherIsParent = $this->isOwner($user, $mother);
                return $fatherIsParent || $motherIsParent;
            case "App\\Models\\Monitoring":
                $parent = MatchModel::findOrFail($model->match_id);
                return $this->isOwner($user, $parent);
            case "App\\Models\\Target":
                $parent = MonitoringModel::findOrFail($model->monitoring_id);
                return $this->isOwner($user, $parent);
            case "App\\Models\\ProgressUpdate":
                $parent = MonitoringModel::findOrFail($model->monitoring_id);
                return $this->isOwner($user, $parent);
            case "App\\Models\\SatelliteMap":
                $parent = MonitoringModel::findOrFail($model->monitoring_id);
                return $this->isOwner($user, $parent);
            default:
                throw new Exception();
        }
    }

    protected function isVisible(?UserModel $user, ?Object $model): bool
    {
        if ($this->isGuest($user) || !((bool) $model)) {
            return false;
        }
        switch (get_class($model)) {
            case "App\\Models\\Offer":
            case "App\\Models\\Pitch":
                $visibility = $model->visibility;
                break;
            case "App\\Models\\OfferContact":
            case "App\\Models\\OfferDocument":
                $parent = OfferModel::findOrFail($model->offer_id);
                $visibility = $parent->visibility;
                break;
            case "App\\Models\\PitchVersion":
            case "App\\Models\\PitchContact":
            case "App\\Models\\CarbonCertification":
            case "App\\Models\\PitchDocument":
            case "App\\Models\\RestorationMethodMetric":
            case "App\\Models\\TreeSpecies":
                $parent = PitchModel::findOrFail($model->pitch_id);
                $visibility = $parent->visibility;
                break;
            case "App\\Models\\CarbonCertificationVersion":
                $parent = CarbonCertificationModel::findOrFail($model->carbon_certification_id);
                $grandParent = PitchModel::findOrFail($parent->pitch_id);
                $visibility = $grandParent->visibility;
                break;
            case "App\\Models\\PitchDocumentVersion":
                $parent = PitchDocumentModel::findOrFail($model->pitch_document_id);
                $grandParent = PitchModel::findOrFail($parent->pitch_id);
                $visibility = $grandParent->visibility;
                break;
            case "App\\Models\\RestorationMethodMetricVersion":
                $parent = RestorationMethodMetricModel::findOrFail($model->restoration_method_metric_id);
                $grandParent = PitchModel::findOrFail($parent->pitch_id);
                $visibility = $grandParent->visibility;
                break;
            case "App\\Models\\TreeSpeciesVersion":
                $parent = TreeSpeciesModel::findOrFail($model->tree_species_id);
                $grandParent = PitchModel::findOrFail($parent->pitch_id);
                $visibility = $grandParent->visibility;
                break;
            default:
                throw new Exception();
        }
        return !in_array($visibility, ["archived", "finished"]);
    }
}
