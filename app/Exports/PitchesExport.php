<?php

namespace App\Exports;

use App\Helpers\CountryHelper;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * This class finds:
 *     pitches
 * which are:
 *     approved
 *     visible
 *     belonging to organisations which are:
 *         approved
 * from:
 *     the beginning of time
 */
class PitchesExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'organisation_id',
            'organisation_name',
            'organisation_category',
            'organisation_type',
            'organisation_country',
            'pitch_id',
            'pitch_name',
            'pitch_created_at',
            'pitch_visibility',
            'pitch_status',
            'pitch_land_types',
            'pitch_land_ownerships',
            'pitch_land_size',
            'pitch_land_continent',
            'pitch_land_country',
            'pitch_restoration_methods',
            'pitch_restoration_goals',
            'pitch_funding_sources',
            'pitch_funding_amount',
            'pitch_revenue_drivers',
            'pitch_estimated_timespan',
            'pitch_long_term_engagement',
            'pitch_reporting_frequency',
            'pitch_reporting_level',
            'pitch_local_community_involvement',
            'pitch_training_involved',
            'pitch_training_amount_people',
            'pitch_amount_of_people_nearby',
            'pitch_amount of_people_abroad',
            'pitch_amount_of_employees',
            'pitch_amount_of_volunteers',
            'pitch_benefited_people',
            'pitch_price_per_tree',
            'pitch_funding_bracket',
            'pitch_funding_status',
            'pitch_funding_status_updated_at',
            'tree_species_count',
        ];
    }

    public function collection(): Collection
    {
        $latestVersions = DB::table('pitch_versions')
                ->select(DB::raw('pitch_versions.*'))
                ->joinSub(
                    DB::table('pitch_versions')
                        ->select('pitch_id', DB::raw('MAX(id) as id'))->groupBy('pitch_id'),
                    'latest_version',
                    function ($join) {
                        $join->on('pitch_versions.id', '=', 'latest_version.id');
                    }
                );

        $query = DB::table('pitches')
            ->select([
                'organisations.id AS organisation_id',
                'organisation_versions.name AS organisation_name',
                'organisation_versions.category AS organisation_category',
                'organisation_versions.type AS organisation_type',
                'organisation_versions.country AS organisation_country',
                'pitches.id AS pitch_id',
                'latest_pitch_versions.name AS pitch_name',
                'pitches.created_at AS pitched_created_at',
                'pitches.visibility AS pitch_visibility',
                'latest_pitch_versions.status AS pitch_status',
                'latest_pitch_versions.land_types AS pitch_land_types',
                'latest_pitch_versions.land_ownerships AS pitch_land_owner',
                'latest_pitch_versions.land_size AS pitch_land_size',
                'latest_pitch_versions.land_continent AS pitch_land_continent',
                'latest_pitch_versions.land_country AS pitch_land_country',
                'latest_pitch_versions.restoration_methods AS pitch_restoration_methods',
                'latest_pitch_versions.restoration_goals AS pitch_restoration_goals',
                'latest_pitch_versions.funding_sources AS pitch_funding_sources',
                'latest_pitch_versions.funding_amount AS pitch_funding_amount',
                'latest_pitch_versions.revenue_drivers AS pitch_revenue_drivers',
                'latest_pitch_versions.estimated_timespan AS pitch_estimated_timespan',
                'latest_pitch_versions.long_term_engagement AS pitch_long_term_engagement',
                'latest_pitch_versions.reporting_frequency AS pitch_reporting_frequency',
                'latest_pitch_versions.reporting_level AS pitch_reporting_level',
                'latest_pitch_versions.local_community_involvement AS pitch_local_community_involvement',
                'latest_pitch_versions.training_involved AS pitch_training_involved',
                'latest_pitch_versions.training_amount_people AS pitch_training_amount_people',
                'latest_pitch_versions.people_amount_nearby AS pitch_people_amount_nearby',
                'latest_pitch_versions.people_amount_abroad AS pitch_people_amount_abroad',
                'latest_pitch_versions.people_amount_employees AS pitch_people_amount_employees',
                'latest_pitch_versions.people_amount_volunteers AS pitch_people_amount_volunteers',
                'latest_pitch_versions.benefited_people AS pitch_benefited_people',
                'latest_pitch_versions.price_per_tree AS pitch_price_per_tree',
                'latest_pitch_versions.funding_bracket AS pitch_funding_bracket',
                'pitches.visibility AS pitch_funding_status',
                'pitches.updated_at AS pitch_funding_status_update_at',
                DB::raw('
                    (
                        SELECT COUNT(`pitch_id`)
                        FROM   `tree_species`
                        WHERE  `pitch_id` = `pitches`.`id`  AND  `tree_species`.`deleted_at` IS NULL
                    ) AS `tree_species_count`
                '),
            ])

            ->joinSub(
                $latestVersions,
                'latest_pitch_versions',
                function ($join) {
                    $join->on('pitches.id', '=', 'latest_pitch_versions.pitch_id');
                }
            )

            ->join('organisations', function (JoinClause $join) {
                $join->on('pitches.organisation_id', '=', 'organisations.id');
            })
            ->join('organisation_versions', function (JoinClause $join) {
                $join->on('organisations.id', '=', 'organisation_versions.organisation_id')
                    ->where('organisation_versions.status', '=', 'approved');
            })
            ->whereNotIn('pitches.visibility', ['archived', 'finished'])
            ->orderBy('latest_pitch_versions.created_at');

        $collection = $query->get();
        $collection = CountryHelper::codesToNames($collection, ['organisation_country', 'pitch_land_country']);

        return $collection->unique('pitch_id');
    }
}
