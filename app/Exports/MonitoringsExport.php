<?php

namespace App\Exports;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * This class finds:
 *     monitorings
 * which have:
 *     accepted targets
 * from:
 *     the beginning of time
 */
class MonitoringsExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'monitoring_id',
            'match_id',
            'monitoring_initiator',
            'monitoring_stage',
            'monitoring_negotiating',
            'monitoring_create_date',
            'pitch_id',
            'pitch_name',
            'funding_offer_id',
            'funding_offer_name',
            'target_start_date',
            'target_completion_date',
            'target_funding_amount',
            'target_geojson',
            'target_trees_planted_total',
            'target_non-trees_planted_total',
            'target_survival_rate',
            'target_hectares_planted',
            'target_hectares_restored',
            'target_carbon_captured',
            'target_nurseries_supported',
            'target_nursery_production',
            'target_short-term_jobs_created',
            'target_long-term_jobs_created',
            'target_volunteers_engaged',
            'target_people_trained',
            'target_benefited_people',
        ];
    }

    public function collection(): Collection
    {
        $query = DB::table('monitorings')
            ->selectRaw("
                monitorings.id AS monitoring_id,
                matches.id AS match_id,
                monitorings.initiator AS monitoring_initiator,
                monitorings.stage AS monitoring_stage,
                monitorings.negotiating AS monitoring_negotiating,
                monitorings.created_at AS monitoring_created_at,
                pitches.id AS pitch_id,
                pitch_versions.name AS pitch_name,
                offers.id AS offer_id,
                offers.name AS offer_name,
                targets.start_date AS target_start_date,
                targets.finish_date AS target_finish_date,
                targets.funding_amount AS target_funding_amount,
                targets.land_geojson AS target_land_geojson,
                JSON_EXTRACT(targets.data, '$.trees_planted') AS target_trees_planted,
                JSON_EXTRACT(targets.data, '$.non_trees_planted') AS target_non_trees_planted,
                JSON_EXTRACT(targets.data, '$.survival_rate') AS target_survival_rate,
                JSON_EXTRACT(targets.data, '$.land_size_planted') AS target_land_size_planted,
                JSON_EXTRACT(targets.data, '$.land_size_restored') AS target_land_size_restored,
                JSON_EXTRACT(targets.data, '$.carbon_captured') AS target_carbon_captured,
                JSON_EXTRACT(targets.data, '$.supported_nurseries') AS target_supported_nurseries,
                JSON_EXTRACT(targets.data, '$.nurseries_production_amount') AS target_nurseries_production_amount,
                JSON_EXTRACT(targets.data, '$.short_term_jobs_amount') AS target_short_term_jobs_amount,
                JSON_EXTRACT(targets.data, '$.long_term_jobs_amount') AS target_long_term_jobs_amount,
                JSON_EXTRACT(targets.data, '$.volunteers_amount') AS target_volunteers_amount,
                JSON_EXTRACT(targets.data, '$.training_amount') AS target_training_amount,
                JSON_EXTRACT(targets.data, '$.benefited_people') AS target_benefited_people
            ")
            ->join('targets', function (JoinClause $join) {
                $join->on('monitorings.id', '=', 'targets.monitoring_id')
                    ->whereNotNull('targets.accepted_at');
            })
            ->join('matches', function (JoinClause $join) {
                $join->on('monitorings.match_id', '=', 'matches.id');
            })
            ->join('interests', function (JoinClause $join) {
                $join->on('matches.primary_interest_id', '=', 'interests.id');
            })
            ->join('offers', function (JoinClause $join) {
                $join->on('interests.offer_id', '=', 'offers.id');
            })
            ->join('pitches', function (JoinClause $join) {
                $join->on('interests.pitch_id', '=', 'pitches.id');
            })
            ->join('pitch_versions', function (JoinClause $join) {
                $join->on('pitches.id', '=', 'pitch_versions.pitch_id')
                    ->where('pitch_versions.status', '=', 'approved');
            })
            ->where('monitorings.stage', '=', 'accepted_targets')
            ->orderBy('monitorings.created_at');

        return $query->get();
    }
}
