<?php

namespace App\Helpers;

use App\Models\DueSubmission;
use App\Models\Programme;
use App\Models\Site;
use App\Models\SiteSubmission;
use App\Models\Submission;
use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;

class DeletionHelper
{
    public static function deleteProgrammeAndRelations(Programme $programme): Programme
    {
        $programme->aim()->delete();
        $programme->programmeTreeSpecies()->delete();
        $programme->csvImports()->delete();
        $programme->media()->delete();
        $programme->programmeInvites()->delete();
        $programme->satelliteMonitors()->delete();
        $programme->editHistories()->delete();
        $programme->socioeconomicBenefits()->delete();
        $programme->dueSubmissions()->each(function (DueSubmission $dueSubmission) {
            DeletionHelper::deleteDueSubmissionAndRelations($dueSubmission);
        });
        $programme->users()->detach();

        $programme->sites()->each(function (Site $site) {
            DeletionHelper::deleteSiteAndRelations($site);
        });

        $programme->submissions()->each(function (Submission $submission) {
            DeletionHelper::deleteProgrammeSubmissionAndRelations($submission);
        });

        $programme->delete();

        return $programme;
    }

    public static function deleteSiteAndRelations(Site $site): Site
    {
        $site->siteRestorationMethods()->delete();
        $site->landTenures()->delete();
        $site->siteTreeSpecies()->delete();
        $site->csvImports()->delete();
        $site->media()->delete();
        $site->satelliteMonitors()->delete();
        $site->seedDetails()->delete();
        $site->invasives()->delete();
        $site->socioeconomicBenefits()->delete();
        $site->dueSubmissions()->each(function (DueSubmission $dueSubmission) {
            DeletionHelper::deleteDueSubmissionAndRelations($dueSubmission);
        });
        $site->submissions()->each(function (SiteSubmission $siteSubmission) {
            DeletionHelper::deleteSiteSubmissionAndRelations($siteSubmission);
        });
        $site->delete();

        return $site;
    }

    public static function deleteProgrammeSubmissionAndRelations(Submission $submission): Submission
    {
        $submission->mediaUploads()->delete();
        $submission->programmeTreeSpecies()->delete();
        $submission->socioeconomicBenefits()->delete();
        $submission->csvImports()->delete();
        $submission->delete();

        return $submission;
    }

    public static function deleteSiteSubmissionAndRelations(SiteSubmission $siteSubmission): SiteSubmission
    {
        $siteSubmission->siteTreeSpecies()->delete();
        $siteSubmission->directSeedings()->delete();
        $siteSubmission->disturbances()->delete();
        $siteSubmission->socioeconomicBenefits()->delete();
        $siteSubmission->mediaUploads()->delete();
        $siteSubmission->delete();

        return $siteSubmission;
    }

    public static function deleteDueSubmissionAndRelations(DueSubmission $submission): DueSubmission
    {
        $submission->drafts()->delete();
        $submission->delete();

        return $submission;
    }

    public static function deleteTerrafundProgrammeAndRelations(TerrafundProgramme $terrafundProgramme): TerrafundProgramme
    {
        $terrafundProgramme->terrafundTreeSpecies()->delete();
        $terrafundProgramme->terrafundCsvImports()->delete();
        $terrafundProgramme->terrafundFiles()->delete();
        $terrafundProgramme->terrafundProgrammeInvites()->delete();
        $terrafundProgramme->satelliteMonitors()->delete();
        $terrafundProgramme->editHistories()->delete();
        $terrafundProgramme->terrafundDueSubmissions()->each(function (TerrafundDueSubmission $dueSubmission) {
            DeletionHelper::deleteTerrafundDueSubmissionAndRelations($dueSubmission);
        });
        $terrafundProgramme->users()->detach();

        $terrafundProgramme->terrafundSites()->each(function (TerrafundSite $site) {
            DeletionHelper::deleteTerrafundSiteAndRelations($site);
        });

        $terrafundProgramme->terrafundNurseries()->each(function (TerrafundNursery $nursery) {
            DeletionHelper::deleteTerrafundNurseryAndRelations($nursery);
        });

        $terrafundProgramme->terrafundProgrammeSubmissions()->each(function (TerrafundProgrammeSubmission $submission) {
            DeletionHelper::deleteTerrafundProgrammeSubmissionAndRelations($submission);
        });

        $terrafundProgramme->delete();

        return $terrafundProgramme;
    }

    public static function deleteTerrafundDueSubmissionAndRelations(TerrafundDueSubmission $terrafundDueSubmission): TerrafundDueSubmission
    {
        $terrafundDueSubmission->drafts()->delete();
        $terrafundDueSubmission->delete();

        return $terrafundDueSubmission;
    }

    public static function deleteTerrafundProgrammeSubmissionAndRelations(TerrafundProgrammeSubmission $terrafundProgrammeSubmission): TerrafundProgrammeSubmission
    {
        $terrafundProgrammeSubmission->terrafundFiles()->delete();
        $terrafundProgrammeSubmission->delete();

        return $terrafundProgrammeSubmission;
    }

    public static function deleteTerrafundSiteAndRelations(TerrafundSite $site): TerrafundSite
    {
        $site->terrafundFiles()->delete();
        $site->terrafundTreeSpecies()->delete();
        $site->terrafundDueSubmissions()->each(function (TerrafundDueSubmission $dueSubmission) {
            DeletionHelper::deleteTerrafundDueSubmissionAndRelations($dueSubmission);
        });
        $site->terrafundSiteSubmissions()->each(function (TerrafundSiteSubmission $siteSubmission) {
            DeletionHelper::deleteTerrafundSiteSubmissionAndRelations($siteSubmission);
        });
        $site->delete();

        return $site;
    }

    public static function deleteTerrafundSiteSubmissionAndRelations(TerrafundSiteSubmission $terrafundSiteSubmission): TerrafundSiteSubmission
    {
        $terrafundSiteSubmission->terrafundTreeSpecies()->delete();
        $terrafundSiteSubmission->terrafundNoneTreeSpecies()->delete();
        $terrafundSiteSubmission->terrafundFiles()->delete();
        $terrafundSiteSubmission->disturbances()->delete();
        $terrafundSiteSubmission->delete();

        return $terrafundSiteSubmission;
    }

    public static function deleteTerrafundNurseryAndRelations(TerrafundNursery $terrafundNursery): TerrafundNursery
    {
        $terrafundNursery->terrafundFiles()->delete();
        $terrafundNursery->terrafundTreeSpecies()->delete();
        $terrafundNursery->terrafundDueSubmissions()->each(function (TerrafundDueSubmission $dueSubmission) {
            DeletionHelper::deleteTerrafundDueSubmissionAndRelations($dueSubmission);
        });
        $terrafundNursery->terrafundNurserySubmissions()->each(function (TerrafundNurserySubmission $terrafundNurserySubmission) {
            DeletionHelper::deleteTerrafundNurserySubmissionAndRelations($terrafundNurserySubmission);
        });
        $terrafundNursery->delete();

        return $terrafundNursery;
    }

    public static function deleteTerrafundNurserySubmissionAndRelations(TerrafundNurserySubmission $terrafundNurserySubmission): TerrafundNurserySubmission
    {
        $terrafundNurserySubmission->terrafundFiles()->delete();
        $terrafundNurserySubmission->delete();

        return $terrafundNurserySubmission;
    }
}
