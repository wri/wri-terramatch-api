<?php

namespace App\Helpers;

use App\Models\Terrafund\TerrafundFile;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Terrafund\TerrafundSiteSubmission;
use Illuminate\Database\Eloquent\Builder;
use League\Csv\Writer;

class TerrafundExportHelper
{
    public static function generateSiteSubmissionCsv(Builder $submissionQuery): Object
    {
        $header = [
            'Site ID', 'Project ID', 'Site Name', 'Submission Date', 'Due Date',
            'Project Name', 'Total Number of Trees to be Grown (Project)',
            'Total Hectacres To be Restored (Project)',
            'Site Start Date', 'Site End Date', 'Hecatres of Land',
            'Intervention Type', 'Hectares of Land restored by intervention',
            'Have any major disturbances take place on this site within the past 10 years?',
            'Land Tenure Type',
            'Tree Species', 'Tree Species Amount', 'Sum of Tree Species',
            'Non Tree Species', 'Non Tree Species Amount',
            'Disturbance Type', 'Disturance Description',
        ];
        $records = [];

        $submissionQuery->chunkById(100, function ($submissions) use (&$records) {
            $submissions->each(function (TerrafundSiteSubmission $submission) use (&$records) {
                $records[] = [
                    $submission->terrafund_site_id,
                    $submission->terrafundSite->terrafundProgramme->id,
                    $submission->terrafundSite->name,
                    $submission->created_at,
                    $submission->terrafundDueSubmission ? $submission->terrafundDueSubmission->due_at : null,
                    $submission->terrafundSite->terrafundProgramme->name,
                    $submission->terrafundSite->terrafundProgramme->trees_planted,
                    $submission->terrafundSite->terrafundProgramme->total_hectares_restored,
                    $submission->terrafundSite->start_date,
                    $submission->terrafundSite->end_date,
                    $submission->terrafundSite->hectares_to_restore,
                    implode('|', $submission->terrafundSite->restoration_methods),
                    $submission->terrafundSite->landscape_community_contribution,
                    $submission->terrafundSite->disturbances,
                    implode('|', $submission->terrafundSite->land_tenures),
                    $submission->terrafundTreeSpecies->pluck('name')->implode('|'),
                    $submission->terrafundTreeSpecies->pluck('amount')->implode('|'),
                    $submission->terrafundTreeSpecies->pluck('amount')->sum(),
                    $submission->terrafundNoneTreeSpecies->pluck('name')->implode('|'),
                    $submission->terrafundNoneTreeSpecies->pluck('amount')->implode('|'),
                    $submission->disturbances->pluck('type')->implode('|'),
                    $submission->disturbances->pluck('description')->implode('|'),
                ];
            });
        });

        $csv = Writer::createFromString();
        $csv->insertOne($header);
        $csv->insertAll($records);

        return $csv;
    }

    public static function generateNurserySubmissionCsv(Builder $submissionQuery): Object
    {
        $header = [
            'Nursery ID', 'Project ID', 'Nursery Name', 'Nursery Start Date', 'Nursery End Date',
            'Submission Date', 'Due Date',
            'Organisation Name', 'Project Name', 'Seedlings Target',
            'Tree Species', 'Tree Species Count', 'Activities',
            'Contribtion to Restoration Goals', 'Submission Date', 'Seedlings or Young Trees',
            'Interesting Facts', 'Site Prep',
        ];
        $records = [];

        $submissionQuery->chunkById(100, function ($submissions) use (&$records) {
            $submissions->each(function (TerrafundNurserySubmission $submission) use (&$records) {
                $records[] = [
                    $submission->terrafund_nursery_id,
                    $submission->terrafundNursery->terrafundProgramme->id,
                    $submission->terrafundNursery->name,
                    $submission->terrafundNursery->start_date,
                    $submission->terrafundNursery->end_date,
                    $submission->created_at,
                    $submission->terrafundDueSubmission ? $submission->terrafundDueSubmission->due_at : null,
                    $submission->terrafundNursery->terrafundProgramme->organisation->approved_version ?
                        $submission->terrafundNursery->terrafundProgramme->organisation->approved_version->name :
                        null,
                    $submission->terrafundNursery->terrafundProgramme->name,
                    $submission->terrafundNursery->seedling_grown,
                    $submission->terrafundNursery->terrafundTreeSpecies->pluck('name')->implode('|'),
                    $submission->terrafundNursery->terrafundTreeSpecies->pluck('amount')->implode('|'),
                    $submission->terrafundNursery->type,
                    $submission->terrafundNursery->planting_contribution,
                    $submission->created_at,
                    $submission->seedlings_young_trees,
                    $submission->interesting_facts,
                    $submission->site_prep,
                ];
            });
        });

        $csv = Writer::createFromString();
        $csv->insertOne($header);
        $csv->insertAll($records);

        return $csv;
    }

    public static function generateProgrammeSubmissionCsv(Builder $submissionQuery): Object
    {
        $header = [
            'Programme ID', 'Programme Name', 'Project Country', 'Organisation ID',
            'Organisation Name', 'Organisation FT Permanent Employees', 'Organisation FT Seasonal Employees',
            'Organisation PT Seasonal Employees', 'Submission Date', 'Due Date',
            'Jobs to be created', 'Trees Planted', 'Hectares Restored',
            'Project Objectives', 'Socioeocnomic Goals', 'Project Budget', 'Project Status',
            'Landscape & Community Contribution', 'Top Three Successes',
            'Challenges Faced (& Lessons)', 'Lessons Learned', 'Maintenance & Monitoring Activities', 'Significant Change',
            'Percentage Survival to Date', 'Survival Calculation', 'Survival Comparison',
            'Total Number of Jobs Created', 'Description of New Jobs',
            'FT Women', 'FT Men', 'FT Youth', 'FT Non Youth', 'FT Smallholder', 'FT Total',
            'PT Women', 'PT Men', 'PT Youth', 'PT Non Youth', 'PT Smallholder', 'PT Total',
            'Seasonal Women', 'Seasonal Men', 'Seasonal Youth', 'Seasonal Smallholder',
            'Total Seasonal', 'Volunteer Women', 'Volunteer Men', 'Volunteer Youth', 'Volunteer Non-Youth',
            'Volunteer Smallholder', 'Total Volunteer', 'Volunteer description',
            'Total Beneficiaries',
            'Beneficiaries Description',
            'Beneficaries Women',
            'Beneficaries Men',
            'Beneficaries Youth',
            'Beneficaries Non Youth',
            'Beneficaries Small Holder Farmers',
            'Beneficaries Large Scale Farmers',
            'People with annual income increase',
            'People with annual income increase description',
            'People with knowledge or skills increased',
            'People with knowledge or skills increased description',
            'Additional documentation',
            /** From here is fields the client hasn't included in the example */
            'Planted Trees',
            'New Volunteers',
            'Shared Drive Link',
        ];
        $records = [];

        $submissionQuery->chunkById(100, function ($submissions) use (&$records) {
            $submissions->each(function (TerrafundProgrammeSubmission $submission) use (&$records) {
                $records[] = [
                    $submission->terrafund_programme_id,
                    $submission->terrafundProgramme->name,
                    $submission->terrafundProgramme->project_country,
                    $submission->terrafundProgramme->organisation->approved_version ?
                        $submission->terrafundProgramme->organisation->approved_version->id :
                        null,
                    $submission->terrafundProgramme->organisation->approved_version ?
                        $submission->terrafundProgramme->organisation->approved_version->name :
                        null,
                    $submission->terrafundProgramme->organisation->approved_version ?
                        $submission->terrafundProgramme->organisation->approved_version->full_time_permanent_employees :
                        null,
                    $submission->terrafundProgramme->organisation->approved_version ?
                        $submission->terrafundProgramme->organisation->approved_version->seasonal_employees :
                        null,
                    $submission->terrafundProgramme->organisation->approved_version ?
                        $submission->terrafundProgramme->organisation->approved_version->part_time_permanent_employees :
                        null,
                    $submission->created_at,
                    $submission->terrafundDueSubmission ? $submission->terrafundDueSubmission->due_at : null,
                    $submission->terrafundProgramme->jobs_created,
                    $submission->terrafundProgramme->trees_planted,
                    $submission->terrafundProgramme->total_hectares_restored,
                    $submission->terrafundProgramme->objectives,
                    $submission->terrafundProgramme->socioeconomic_goals,
                    $submission->terrafundProgramme->budget,
                    $submission->terrafundProgramme->status,
                    $submission->landscape_community_contribution,
                    $submission->top_three_successes,
                    $submission->challenges_and_lessons,
                    $submission->lessons_learned,
                    $submission->maintenance_and_monitoring_activities,
                    $submission->significant_change,
                    $submission->percentage_survival_to_date,
                    $submission->survival_calculation,
                    $submission->survival_comparison,
                    $submission->new_jobs_created,
                    $submission->new_jobs_description,
                    $submission->ft_women,
                    $submission->ft_men,
                    $submission->ft_youth,
                    $submission->full_time_jobs_35plus,
                    $submission->ft_smallholder_farmers,
                    $submission->ft_total,
                    $submission->pt_women,
                    $submission->pt_men,
                    $submission->pt_youth,
                    $submission->part_time_jobs_35plus,
                    $submission->pt_smallholder_farmers,
                    $submission->pt_total,
                    $submission->seasonal_women,
                    $submission->seasonal_men,
                    $submission->seasonal_youth,
                    $submission->seasonal_smallholder_farmers,
                    $submission->seasonal_total,
                    $submission->volunteer_women,
                    $submission->volunteer_men,
                    $submission->volunteer_youth,
                    $submission->volunteer_35plus,
                    $submission->volunteer_smallholder_farmers,
                    $submission->volunteer_total,
                    $submission->volunteers_work_description,
                    $submission->beneficiaries,
                    $submission->beneficiaries_description,
                    $submission->women_beneficiaries,
                    $submission->men_beneficiaries,
                    $submission->youth_beneficiaries,
                    $submission->beneficiaries_35plus,
                    $submission->smallholder_beneficiaries,
                    $submission->large_scale_beneficiaries,
                    $submission->people_annual_income_increased,
                    $submission->income_increase_description,
                    $submission->people_knowledge_skills_increased,
                    $submission->skills_knowledge_description,
                    $submission->terrafundDocumentsFiles->pluck('upload')->implode('|'),
                    $submission->planted_trees,
                    $submission->new_volunteers,
                    $submission->shared_drive_link,
                ];
            });
        });

        $csv = Writer::createFromString();
        $csv->insertOne($header);
        $csv->insertAll($records);

        return $csv;
    }

    public static function generateProgrammeImagesZip(TerrafundProgramme $terrafundProgramme): Object
    {
        $terrafundSiteIds = $terrafundProgramme->terrafundSites->pluck('id');
        $terrafundNurseryIds = $terrafundProgramme->terrafundNurseries->pluck('id');
        $terrafundProgrammeSubmissionIds = $terrafundProgramme->terrafundProgrammeSubmissions->pluck('id');
        $terrafundSiteSubmissionIds = TerrafundSiteSubmission::whereIn('terrafund_site_id', $terrafundSiteIds)->pluck('id');
        $terrafundNurserySubmissionIds = TerrafundNurserySubmission::whereIn('terrafund_nursery_id', $terrafundNurseryIds)->pluck('id');

        $files = TerrafundFile::query()
            ->where(function ($query) use ($terrafundProgramme) {
                $query
                    ->terrafundProgramme()
                    ->where('fileable_id', $terrafundProgramme->id);
            })
            ->orWhere(function ($query) use ($terrafundProgrammeSubmissionIds) {
                $query
                    ->terrafundProgrammeSubmission()
                    ->whereIn('fileable_id', $terrafundProgrammeSubmissionIds);
            })
            ->orWhere(function ($query) use ($terrafundSiteIds) {
                $query
                    ->terrafundSite()
                    ->whereIn('fileable_id', $terrafundSiteIds);
            })
            ->orWhere(function ($query) use ($terrafundSiteSubmissionIds) {
                $query
                    ->terrafundSiteSubmission()
                    ->whereIn('fileable_id', $terrafundSiteSubmissionIds);
            })
            ->orWhere(function ($query) use ($terrafundNurseryIds) {
                $query
                    ->terrafundNursery()
                    ->whereIn('fileable_id', $terrafundNurseryIds);
            })
            ->orWhere(function ($query) use ($terrafundNurserySubmissionIds) {
                $query
                    ->terrafundNurserySubmission()
                    ->whereIn('fileable_id', $terrafundNurserySubmissionIds);
            })
            ->get();

        return $files;
    }
}
