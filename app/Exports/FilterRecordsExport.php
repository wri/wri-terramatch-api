<?php

namespace App\Exports;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * This class finds:
 *     filter records
 * which are:
 *     belonging to organisations which are:
 *         approved
 *     created in the last month
 */
class FilterRecordsExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'filter_record_id',
            'filter_record_initiator',
            'organisation_id',
            'organisation_name',
            'organisation_category',
            'organisation_country',
            'used_land_types',
            'used_land_ownerships',
            'used_land_size',
            'used_land_continent',
            'used_land_country',
            'used_restoration_methods',
            'used_restoration_goals',
            'used_funding_sources',
            'used_funding_bracket',
            'used_long_term_engagement',
            'used_reporting_frequency',
            'used_reporting_level',
            'used_sustainable_development_goals',
            'used_price_per_tree',
            'filter_record_created_at',
        ];
    }

    public function collection(): Collection
    {
        $query = DB::table('filter_records')
            ->select([
                'filter_records.id AS filter_record_id',
                'filter_records.type AS filter_record_initiator',
                'filter_records.organisation_id AS organisation_id',
                'organisation_versions.name AS organisation_name',
                'organisation_versions.category AS organisation_category',
                'organisation_versions.country AS organisation_country',
                'filter_records.land_types AS used_land_types',
                'filter_records.land_ownerships AS used_land_ownerships',
                'filter_records.land_size AS used_land_size',
                'filter_records.land_continent AS used_land_continent',
                'filter_records.land_country AS used_land_country',
                'filter_records.restoration_methods AS used_restoration_methods',
                'filter_records.restoration_goals AS used_restoration_goals',
                'filter_records.funding_sources AS used_funding_sources',
                'filter_records.funding_bracket AS used_funding_bracket',
                'filter_records.long_term_engagement AS used_long_term_engagement',
                'filter_records.reporting_frequency AS used_reporting_frequency',
                'filter_records.reporting_level AS used_reporting_level',
                'filter_records.sustainable_development_goals AS used_sustainable_development_goals',
                'filter_records.price_per_tree AS used_price_per_tree',
                'filter_records.created_at AS filter_record_created_at',
            ])
            ->join('organisations', function (JoinClause $join) {
                $join->on('filter_records.organisation_id', '=', 'organisations.id');
            })
            ->join('organisation_versions', function (JoinClause $join) {
                $join->on('organisations.id', '=', 'organisation_versions.organisation_id')
                    ->where('organisation_versions.status', '=', 'approved');
            })
            ->whereDate('filter_records.created_at', '>=', Carbon::now()->subDays(28)->toDateString())
            ->orderBy('filter_records.created_at');

        return $query->get();
    }
}
