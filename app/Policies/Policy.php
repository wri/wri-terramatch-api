<?php

namespace App\Policies;

use App\Models\User;
use Exception;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\App;

abstract class Policy
{
    use HandlesAuthorization;

    protected function isGuest(?User $user): bool
    {
        return !((bool) $user);
    }

    protected function isUser(?User $user): bool
    {
        return !$this->isGuest($user) && $user->role == "user";
    }

    protected function isAdmin(?User $user): bool
    {
        return !$this->isGuest($user) && $user->role == "admin";
    }

    protected function isOrphanedUser(?User $user): bool
    {
        return $this->isUser($user) && !((bool) $user->organisation_id);
    }

    protected function isVerifiedUser(?User $user): bool
    {
        return $this->isUser($user) && (bool) $user->email_address_verified_at;
    }

    protected function isVerifiedAdmin(?User $user): bool
    {
        return $this->isAdmin($user) && (bool) $user->email_address_verified_at;
    }

    protected function isFullUser(?User $user): bool
    {
        return !$this->isOrphanedUser($user) && $this->isVerifiedUser($user);
    }

    protected function hasApprovedOrganisation(?User $user): bool
    {
        return (bool) $user->organisation->approvedVersion;
    }

    protected function isOwner(?User $user, ?object $model): bool
    {
        if ($this->isGuest($user) || !((bool) $model)) {
            return false;
        }
        switch (get_class($model)) {
            case "App\\Models\\User":
                return $user->id == $model->id;
            case "App\\Models\\Admin":
                return $user->id == $model->id;
            case "App\\Models\\Organisation":
                return $user->organisation_id == $model->id;
            case "App\\Models\\OrganisationVersion":
                return $user->organisation_id == $model->organisation_id;
            case "App\\Models\\Upload":
                return $user->id == $model->user_id;
            case "App\\Models\\TeamMember":
                return $user->organisation_id == $model->organisation_id;
            case "App\\Models\\OrganisationDocument":
                return $user->organisation_id == $model->organisation_id;
            case "App\\Models\\OrganisationDocumentVersion":
                $parent = App::make("App\\Models\\OrganisationDocument")->findOrFail($model->organisation_document_id);
                return $user->organisation_id == $parent->organisation_id;
            case "App\\Models\\Offer":
                return $user->organisation_id == $model->organisation_id;
            case "App\\Models\\OfferContact":
                $parent = App::make("App\\Models\\Offer")->findOrFail($model->offer_id);
                return $user->organisation_id == $parent->organisation_id;
            case "App\\Models\\OfferDocument":
                $parent = App::make("App\\Models\\Offer")->findOrFail($model->offer_id);
                return $user->organisation_id == $parent->organisation_id;
            case "App\\Models\\Pitch":
                return $user->organisation_id == $model->organisation_id;
            case "App\\Models\\PitchContact":
                $parent = App::make("App\\Models\\Pitch")->findOrFail($model->pitch_id);
                return $user->organisation_id == $parent->organisation_id;
            case "App\\Models\\PitchVersion":
                $parent = App::make("App\\Models\\Pitch")->findOrFail($model->pitch_id);
                return $user->organisation_id == $parent->organisation_id;
            case "App\\Models\\CarbonCertificationVersion":
                $parent = App::make("App\\Models\\CarbonCertification")->findOrFail($model->carbon_certification_id);
                $grandParent = App::make("App\\Models\\Pitch")->findOrFail($parent->pitch_id);
                return $user->organisation_id == $grandParent->organisation_id;
            case "App\\Models\\PitchDocumentVersion":
                $parent = App::make("App\\Models\\PitchDocument")->findOrFail($model->pitch_document_id);
                $grandParent = App::make("App\\Models\\Pitch")->findOrFail($parent->pitch_id);
                return $user->organisation_id == $grandParent->organisation_id;
            case "App\\Models\\RestorationMethodMetricVersion":
                $parent = App::make("App\\Models\\RestorationMethodMetric")->findOrFail($model->restoration_method_metric_id);
                $grandParent = App::make("App\\Models\\Pitch")->findOrFail($parent->pitch_id);
                return $user->organisation_id == $grandParent->organisation_id;
            case "App\\Models\\TreeSpeciesVersion":
                $parent = App::make("App\\Models\\TreeSpecies")->findOrFail($model->tree_species_id);
                $grandParent = App::make("App\\Models\\Pitch")->findOrFail($parent->pitch_id);
                return $user->organisation_id == $grandParent->organisation_id;
            case "App\\Models\\CarbonCertification":
                $parent = App::make("App\\Models\\Pitch")->findOrFail($model->pitch_id);
                return $user->organisation_id == $parent->organisation_id;
            case "App\\Models\\PitchDocument":
                $parent = App::make("App\\Models\\Pitch")->findOrFail($model->pitch_id);
                return $user->organisation_id == $parent->organisation_id;
            case "App\\Models\\RestorationMethodMetric":
                $parent = App::make("App\\Models\\Pitch")->findOrFail($model->pitch_id);
                return $user->organisation_id == $parent->organisation_id;
            case "App\\Models\\TreeSpecies":
                $parent = App::make("App\\Models\\Pitch")->findOrFail($model->pitch_id);
                return $user->organisation_id == $parent->organisation_id;
            case "App\\Models\\Interest":
                return $user->organisation_id == $model->organisation_id;
            case "App\\Models\\Match":
                $parents = [
                    App::make("App\\Models\\Interest")->findOrFail($model->primary_interest_id),
                    App::make("App\\Models\\Interest")->findOrFail($model->secondary_interest_id)
                ];
                return $user->organisation_id == $parents[0]->organisation_id || $user->organisation_id == $parents[1]->organisation_id;
            case "App\\Models\\Notification":
                return $user->id == $model->user_id;
            case "App\\Models\\Device":
                return $user->id == $model->user_id;
            default:
                throw new Exception();
        }
    }

    protected function isCompleted(?User $user, ?object $model): bool
    {
        if ($this->isGuest($user) || !((bool) $model)) {
            return false;
        }
        switch (get_class($model)) {
            // offers
            case "App\\Models\\Offer":
                return $model->completed;
            case "App\\Models\\OfferContact":
                $parent = App::make("App\\Models\\Offer")->findOrFail($model->offer_id);
                return $parent->completed;
            case "App\\Models\\OfferDocument":
                $parent = App::make("App\\Models\\Offer")->findOrFail($model->offer_id);
                return $parent->completed;
            // pitches
            case "App\\Models\\Pitch":
                return $model->completed;
            case "App\\Models\\PitchVersion":
                $parent = App::make("App\\Models\\Pitch")->findOrFail($model->pitch_id);
                return $parent->completed;
            case "App\\Models\\PitchContact":
                $parent = App::make("App\\Models\\Pitch")->findOrFail($model->pitch_id);
                return $parent->completed;
            case "App\\Models\\CarbonCertification":
                $parent = App::make("App\\Models\\Pitch")->findOrFail($model->pitch_id);
                return $parent->completed;
            case "App\\Models\\CarbonCertificationVersion":
                $parent = App::make("App\\Models\\CarbonCertification")->findOrFail($model->carbon_certification_id);
                $grandParent = App::make("App\\Models\\Pitch")->findOrFail($parent->pitch_id);
                return $grandParent->completed;
            case "App\\Models\\PitchDocument":
                $parent = App::make("App\\Models\\Pitch")->findOrFail($model->pitch_id);
                return $parent->completed;
            case "App\\Models\\PitchDocumentVersion":
                $parent = App::make("App\\Models\\PitchDocument")->findOrFail($model->pitch_document_id);
                $grandParent = App::make("App\\Models\\Pitch")->findOrFail($parent->pitch_id);
                return $grandParent->completed;
            case "App\\Models\\RestorationMethodMetric":
                $parent = App::make("App\\Models\\Pitch")->findOrFail($model->pitch_id);
                return $parent->completed;
            case "App\\Models\\RestorationMethodMetricVersion":
                $parent = App::make("App\\Models\\RestorationMethodMetric")->findOrFail($model->restoration_method_metric_id);
                $grandParent = App::make("App\\Models\\Pitch")->findOrFail($parent->pitch_id);
                return $grandParent->completed;
            case "App\\Models\\TreeSpecies":
                $parent = App::make("App\\Models\\Pitch")->findOrFail($model->pitch_id);
                return $parent->completed;
            case "App\\Models\\TreeSpeciesVersion":
                $parent = App::make("App\\Models\\TreeSpecies")->findOrFail($model->tree_species_id);
                $grandParent = App::make("App\\Models\\Pitch")->findOrFail($parent->pitch_id);
                return $grandParent->completed;
            // default
            default:
                throw new Exception();
        }
    }
}
