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
 *     progress updates
 * from:
 *     the beginning of time
 */
class ProgressUpdatesExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'progress_update_id',
            'monitoring_id',
            'progress_update_grouping',
            'progress_update_title',
            'progress_update_breakdown',
            'progress_update_summary',
            'progress_update_created_by_admin',
            'progress_update_created_by_user',
            'progress_update_created_at',
            'progress_update_planting_date',
            'progress_update_trees_planted_total',
            'progress_update_non_trees_planted_total',
            'progress_update_survival_amount_total',
            'progress_update_supported_nurseries',
            'progress_update_survival_rate',
            'progress_update_carbon_captured',
            'progress_update_nurseries_production_amount',
            'progress_update_land_size_planted',
            'progress_update_land_size_restored',
            'progress_update_short_term_jobs_amount_total',
            'progress_update_long_term_jobs_amount_total',
            'progress_update_volunteers_amount_total',
            'progress_update_training_amount_total',
            'progress_update_benefited_people_total',
            'progress_update_mortality_causes',
            'progress_update_challenges_update',
            'progress_update_insights_update',
            'progress_update_biodiversity_update',
        ];
    }

    public function collection(): Collection
    {
        $query = DB::table('progress_updates')
            ->selectRaw("
                progress_updates.id AS progress_update_id,
                progress_updates.monitoring_id AS monitoring_id,
                progress_updates.grouping AS progress_update_grouping,
                progress_updates.title AS progress_update_title,
                progress_updates.breakdown AS progress_update_breakdown,
                progress_updates.summary AS progress_update_summary,
                IF(users.role = 'admin', 'Yes', 'No') AS progress_update_created_by_admin,
                IF(users.role = 'user', progress_updates.created_by, NULL) AS progress_update_created_by_user,
                progress_updates.created_at AS progress_update_created_at,
                JSON_EXTRACT(progress_updates.data, '$.planting_date') AS progress_update_planting_date,
                JSON_EXTRACT(progress_updates.data, '$.trees_planted_total') AS progress_update_trees_planted_total,
                JSON_EXTRACT(progress_updates.data, '$.non_trees_planted_total') AS progress_update_non_trees_planted_total,
                JSON_EXTRACT(progress_updates.data, '$.survival_amount_total') AS progress_update_survival_amount_total,
                JSON_EXTRACT(progress_updates.data, '$.supported_nurseries') AS progress_update_supported_nurseries,
                JSON_EXTRACT(progress_updates.data, '$.survival_rate') AS progress_update_survival_rate,
                JSON_EXTRACT(progress_updates.data, '$.carbon_captured') AS progress_update_carbon_captured,
                JSON_EXTRACT(progress_updates.data, '$.nurseries_production_amount') AS progress_update_nurseries_production_amount,
                JSON_EXTRACT(progress_updates.data, '$.land_size_planted') AS progress_update_land_size_planted,
                JSON_EXTRACT(progress_updates.data, '$.land_size_restored') AS progress_update_land_size_restored,
                JSON_EXTRACT(progress_updates.data, '$.short_term_jobs_amount_total') AS progress_update_short_term_jobs_amount_total,
                JSON_EXTRACT(progress_updates.data, '$.long_term_jobs_amount_total') AS progress_update_long_term_jobs_amount_total,
                JSON_EXTRACT(progress_updates.data, '$.volunteers_amount_total') AS progress_update_volunteers_amount_total,
                JSON_EXTRACT(progress_updates.data, '$.training_amount_total') AS progress_update_training_amount_total,
                JSON_EXTRACT(progress_updates.data, '$.benefited_people_total') AS progress_update_benefited_people_total,
                JSON_EXTRACT(progress_updates.data, '$.mortality_causes') AS progress_update_mortality_causes,
                JSON_EXTRACT(progress_updates.data, '$.challenges_update') AS progress_update_challenges_update,
                JSON_EXTRACT(progress_updates.data, '$.insights_update') AS progress_update_insights_update,
                JSON_EXTRACT(progress_updates.data, '$.biodiversity_update') AS progress_update_biodiversity_update
            ")
            ->join('users', function (JoinClause $join) {
                $join->on('progress_updates.created_by', '=', 'users.id');
            })
            ->orderBy('progress_updates.created_at');

        return $query->get();
    }
}
